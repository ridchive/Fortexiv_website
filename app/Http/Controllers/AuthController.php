<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const EMAIL_VERIFICATION_CODE_MAX_ATTEMPTS = 5;

    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function forgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordResetLink(Request $request, EmailService $emailService): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:100'],
        ]);

        $emailService->sendPasswordResetLink($credentials['email']);

        return back()->with('status', 'If an account exists for that email, a reset link will be sent. If you recently requested one, please wait before trying again.');
    }

    public function resetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email'),
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password has been reset. You can now sign in.');
        }

        return back()->withErrors(['email' => 'This password reset link is invalid or has expired.']);
    }

    public function verificationNotice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->to($this->dashboardUrl($user));
        }

        return view('auth.verify-email');
    }

    public function verifyEmail(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->to($this->dashboardUrl($request->user()));
        }

        $request->fulfill();
        $request->user()->forceFill([
            'email_verification_code_hash' => null,
            'email_verification_code_expires_at' => null,
            'email_verification_code_attempts' => 0,
        ])->save();

        return redirect()->to($this->dashboardUrl($request->user()))->with('status', 'Your email address has been verified.');
    }

    public function resendEmailVerification(Request $request, EmailService $emailService): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->to($this->dashboardUrl($user));
        }

        $emailService->sendEmailVerificationLink($user);

        return back()->with('status', 'A new email verification link has been sent.');
    }

    public function sendEmailVerificationCode(Request $request, EmailService $emailService): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->to($this->dashboardUrl($user));
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'email_verification_code_hash' => Hash::make($code),
            'email_verification_code_expires_at' => now()->addMinutes(10),
            'email_verification_code_attempts' => 0,
        ])->save();

        $emailService->sendEmailVerificationCode($user, $code);

        return back()->with('status', 'A verification code has been sent to your email address.');
    }

    public function verifyEmailCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $result = DB::transaction(function () use ($request, $data): string {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);

            if ($user->hasVerifiedEmail()) {
                return 'verified';
            }

            if ($user->email_verification_code_attempts >= self::EMAIL_VERIFICATION_CODE_MAX_ATTEMPTS) {
                return 'too_many_attempts';
            }

            if (
                ! $user->email_verification_code_hash
                || ! $user->email_verification_code_expires_at
                || $user->email_verification_code_expires_at->isPast()
            ) {
                return 'invalid';
            }

            if (! Hash::check($data['code'], $user->email_verification_code_hash)) {
                $user->increment('email_verification_code_attempts');

                return 'invalid';
            }

            $user->forceFill([
                'email_verification_code_hash' => null,
                'email_verification_code_expires_at' => null,
                'email_verification_code_attempts' => 0,
            ])->save();
            $user->markEmailAsVerified();

            return 'verified';
        });

        if ($result === 'verified') {
            return redirect()->to($this->dashboardUrl($request->user()))
                ->with('status', 'Your email address has been verified.');
        }

        $message = $result === 'too_many_attempts'
            ? 'Too many incorrect codes. Request a new code to try again.'
            : 'The verification code is invalid or has expired.';

        return back()->withErrors(['code' => $message]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password tidak cocok.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        if (! $request->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'Akun ini telah dinonaktifkan.'])->onlyInput('email');
        }

        if (! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended($this->dashboardUrl($request->user()));
    }

    public function registerForm(): View
    {
        return view('auth.register', ['projects' => Project::orderBy('class_name')->get()]);
    }

    public function register(Request $request, EmailService $emailService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', Rule::in(['buyer', 'seller'])],
            'store_name' => ['required_if:role,seller', 'nullable', 'string', 'max:100'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'role' => $data['role'],
            'is_active' => true,
        ]);

        if ($user->role === 'seller') {
            Seller::create([
                'user_id' => $user->id,
                'project_id' => $data['project_id'] ?? null,
                'store_name' => $data['store_name'],
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $emailService->sendEmailVerificationLink($user);

        return redirect()->route('verification.notice')->with('status', 'A verification link has been sent to your email address.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function dashboardUrl(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'seller' => route('seller.dashboard'),
            default => route('home'),
        };
    }
}
