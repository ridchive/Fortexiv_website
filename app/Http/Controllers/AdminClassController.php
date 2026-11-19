<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminClassController extends Controller
{
    public function index(): View
    {
        $classes = Project::query()
            ->with(['seller.user', 'products'])
            ->withCount('products')
            ->orderBy('grade')
            ->orderBy('class_name')
            ->get();

        return view('admin.classes.index', compact('classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'grade' => ['required', 'integer', 'min:1', 'max:1000', Rule::unique('projects', 'grade')],
            'class_name' => ['required', 'string', 'max:100'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'password' => [Rule::requiredIf(filled($request->input('username'))), 'nullable', 'string', 'min:4'],
        ], [
            'grade.required' => 'Nomor akun wajib diisi.',
            'grade.unique' => 'Nomor akun ini sudah digunakan oleh kelas lain. Setiap kelas harus memiliki nomor akun yang berbeda.',
            'username.unique' => 'Username ini sudah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'password.required' => 'Password wajib diisi apabila membuat akun seller.',
            'password.min' => 'Password minimal terdiri dari 4 karakter.',
        ]);

        DB::transaction(function () use ($data) {
            $className = trim($data['class_name']);
            $grade = (int) $data['grade'];

            $articleBody = "## Cerita Proyek {$className}\n\nArtikel dan dokumentasi proyek karya untuk kelas {$className} (Level {$grade}).";

            $project = Project::create([
                'grade' => $grade,
                'class_name' => $className,
                'title' => "Proyek Karya Kelas {$className}",
                'introduction' => "Dokumentasi proyek dan karya siswa {$className} (Level {$grade}).",
                'article_body' => $articleBody,
                'is_published' => true,
            ]);

            if (filled($data['username'] ?? null)) {
                $username = trim($data['username']);
                $email = Str::slug($username) . '@seller.fortexiv.local';

                // Avoid collision if email already exists
                $counter = 1;
                while (User::where('email', $email)->exists()) {
                    $email = Str::slug($username) . $counter . '@seller.fortexiv.local';
                    $counter++;
                }

                $user = User::create([
                    'name' => 'Tim ' . $className,
                    'username' => $username,
                    'email' => $email,
                    'password' => $data['password'],
                    'role' => 'seller',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                Seller::create([
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'store_name' => $className,
                    'description' => 'Akun seller kelas ' . $className,
                ]);
            }
        });

        return redirect()->route('admin.classes.index')->with('status', 'Kelas baru berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $seller = $project->seller;
        $currentUser = $seller?->user;
        $currentUserId = $currentUser?->id;

        $data = $request->validate([
            'grade' => ['required', 'integer', 'min:1', 'max:1000', Rule::unique('projects', 'grade')->ignore($project->id)],
            'class_name' => ['required', 'string', 'max:100'],
            'username' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($currentUserId),
            ],
            'password' => ['nullable', 'string', 'min:4'],
            'remove_account' => ['nullable', 'boolean'],
        ], [
            'grade.required' => 'Nomor akun wajib diisi.',
            'grade.unique' => 'Nomor akun ini sudah digunakan oleh kelas lain. Setiap kelas harus memiliki nomor akun yang berbeda.',
            'username.unique' => 'Username ini sudah digunakan oleh akun lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'password.min' => 'Password minimal terdiri dari 4 karakter.',
        ]);

        DB::transaction(function () use ($data, $project, $currentUser, $seller, $request) {
            $className = trim($data['class_name']);
            $grade = (int) $data['grade'];

            $project->update([
                'grade' => $grade,
                'class_name' => $className,
            ]);

            // If admin opted to remove the account
            if ($request->boolean('remove_account')) {
                if ($seller) {
                    $hasHistory = $seller->products()->exists()
                        || OrderItem::where('seller_id', $seller->id)->exists()
                        || $seller->fulfillments()->exists();

                    if ($hasHistory) {
                        $seller->user?->update([
                            'username' => null,
                            'is_active' => false,
                            'password' => Hash::make(Str::random(40)),
                        ]);
                    } else {
                        try {
                            $user = $seller->user;
                            $seller->delete();
                            $user?->delete();
                        } catch (\Throwable) {
                            $seller->user?->update([
                                'username' => null,
                                'is_active' => false,
                                'password' => Hash::make(Str::random(40)),
                            ]);
                        }
                    }
                }

                return;
            }

            // If username is provided
            if (filled($data['username'] ?? null)) {
                $username = trim($data['username']);

                if ($currentUser) {
                    $currentUser->username = $username;
                    $currentUser->name = 'Tim ' . $className;
                    $currentUser->is_active = true;
                    if (filled($data['password'] ?? null)) {
                        $currentUser->password = $data['password'];
                    } elseif (! $currentUser->password) {
                        $currentUser->password = 'password123';
                    }
                    $currentUser->save();

                    if ($seller) {
                        $seller->update(['store_name' => $className]);
                    }
                } else {
                    $email = Str::slug($username) . '@seller.fortexiv.local';
                    $counter = 1;
                    while (User::where('email', $email)->exists()) {
                        $email = Str::slug($username) . $counter . '@seller.fortexiv.local';
                        $counter++;
                    }

                    $user = User::create([
                        'name' => 'Tim ' . $className,
                        'username' => $username,
                        'email' => $email,
                        'password' => filled($data['password'] ?? null) ? $data['password'] : 'password123',
                        'role' => 'seller',
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);

                    Seller::create([
                        'user_id' => $user->id,
                        'project_id' => $project->id,
                        'store_name' => $className,
                        'description' => 'Akun seller kelas ' . $className,
                    ]);
                }
            }
        });

        $statusMsg = $request->boolean('remove_account')
            ? "Akun login seller untuk kelas {$project->class_name} berhasil dinonaktifkan."
            : "Data kelas {$project->class_name} berhasil diperbarui.";

        return redirect()->route('admin.classes.index')->with('status', $statusMsg);
    }

    public function destroy(Project $project): RedirectResponse
    {
        // Check if there are orders
        $hasOrders = OrderItem::whereHas('seller', fn ($q) => $q->where('project_id', $project->id))->exists();
        if ($hasOrders) {
            return back()->withErrors(['error' => 'Kelas tidak dapat dihapus karena sudah memiliki transaksi pesanan di sistem.']);
        }

        DB::transaction(function () use ($project) {
            foreach ($project->sellers as $seller) {
                $user = $seller->user;
                $seller->delete();
                $user?->delete();
            }

            $project->products()->delete();
            $project->delete();
        });

        return redirect()->route('admin.classes.index')->with('status', 'Kelas berhasil dihapus.');
    }

    public function initializeDefaults(): RedirectResponse
    {
        DB::transaction(function () {
            for ($level = 1; $level <= 11; $level++) {
                $className = (string) $level;

                $existing = Project::where('grade', $level)->where('class_name', $className)->first();
                if (! $existing) {
                    $articleBody = "## Cerita Proyek Kelas {$className}\n\nArtikel dan dokumentasi proyek karya untuk kelas {$className} (Level {$level}). Setiap level memiliki cerita proyek dan karya siswa tersendiri.";

                    Project::create([
                        'grade' => $level,
                        'class_name' => $className,
                        'title' => "Proyek Karya Kelas {$className}",
                        'introduction' => "Dokumentasi karya dan proyek siswa kelas {$className} (Level {$level}).",
                        'article_body' => $articleBody,
                        'is_published' => true,
                    ]);
                }
            }
        });

        return redirect()->route('admin.classes.index')->with('status', '11 Level kelas (Level 1 - 11) berhasil diinisialisasi.');
    }
}
