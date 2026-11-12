<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailCode;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_verification_link_marks_email_as_verified(): void
    {
        $user = User::factory()->unverified()->create();
        $signedUrl = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)
            ->get($signedUrl)
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Your email address has been verified.');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_unsigned_verification_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]))
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_unverified_user_can_resend_verification_email(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->from(route('verification.notice'))
            ->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'A new email verification link has been sent.');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_can_request_a_six_digit_email_verification_code(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->from(route('verification.notice'))
            ->actingAs($user)
            ->post(route('verification.code.send'))
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'A verification code has been sent to your email address.');

        Notification::assertSentTo(
            $user,
            VerifyEmailCode::class,
            fn (VerifyEmailCode $notification): bool => preg_match('/^\d{6}$/', $notification->code) === 1
                && Hash::check($notification->code, $user->fresh()->email_verification_code_hash),
        );
    }

    public function test_valid_email_verification_code_marks_email_as_verified_and_is_removed(): void
    {
        $user = User::factory()->unverified()->create();
        $user->forceFill([
            'email_verification_code_hash' => Hash::make('123456'),
            'email_verification_code_expires_at' => now()->addMinutes(10),
            'email_verification_code_attempts' => 0,
        ])->save();

        $this->actingAs($user)
            ->post(route('verification.code.verify'), ['code' => '123456'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Your email address has been verified.');

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNull($user->fresh()->email_verification_code_hash);
        $this->assertNull($user->fresh()->email_verification_code_expires_at);
    }

    public function test_invalid_email_verification_code_increments_attempts_without_verifying_email(): void
    {
        $user = User::factory()->unverified()->create();
        $user->forceFill([
            'email_verification_code_hash' => Hash::make('123456'),
            'email_verification_code_expires_at' => now()->addMinutes(10),
            'email_verification_code_attempts' => 0,
        ])->save();

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.code.verify'), ['code' => '654321'])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHasErrors('code');

        $this->assertSame(1, $user->fresh()->email_verification_code_attempts);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_expired_email_verification_code_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $user->forceFill([
            'email_verification_code_hash' => Hash::make('123456'),
            'email_verification_code_expires_at' => now()->subSecond(),
            'email_verification_code_attempts' => 0,
        ])->save();

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.code.verify'), ['code' => '123456'])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_email_verification_code_is_rejected_after_five_incorrect_attempts(): void
    {
        $user = User::factory()->unverified()->create();
        $user->forceFill([
            'email_verification_code_hash' => Hash::make('123456'),
            'email_verification_code_expires_at' => now()->addMinutes(10),
            'email_verification_code_attempts' => 5,
        ])->save();

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.code.verify'), ['code' => '123456'])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHasErrors([
                'code' => 'Too many incorrect codes. Request a new code to try again.',
            ]);

        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame(5, $user->fresh()->email_verification_code_attempts);
    }

    public function test_email_verification_code_requires_six_numeric_digits(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.code.verify'), ['code' => 'abc123'])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_verification_notice_offers_email_code_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('name="code"', false)
            ->assertSee('Send verification code');
    }

    public function test_unverified_user_must_verify_before_using_buyer_routes(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_unverified_user_is_sent_to_verification_after_login(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => 'correct-horse-battery-staple',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ])
            ->assertRedirect(route('verification.notice'));

        $this->assertAuthenticatedAs($user);
    }
}
