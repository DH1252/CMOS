<?php

namespace App\Http\Controllers;

use App\Support\UploadedImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user()->loadMissing(['role', 'department']);

        return \Inertia\Inertia::render(
            'pages/ProfileSettingsPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $errorMap = collect(session('errors')?->messages() ?? [])->map(fn ($messages) => $messages[0])->all();

                $props = [
                    'title' => 'Edit Profil',
                    'description' => 'Perbarui identitas akun, foto profil, dan keamanan akses Anda.',
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'roleName' => $user->role_name,
                        'department' => $user->department?->name,
                        'avatarUrl' => $user->avatar_url,
                        'joinedAt' => $user->created_at->toIso8601String(),
                    ],
                    'profileForm' => [
                        'action' => route('profile.update'),
                        'csrfToken' => csrf_token(),
                        'spoofMethod' => 'PUT',
                        'values' => [
                            'name' => old('name', $user->name),
                            'avatarUrl' => $user->avatar_url,
                        ],
                        'errors' => collect($errorMap)->only(['name', 'avatar'])->all(),
                    ],
                    'passwordForm' => [
                        'action' => route('profile.password.update'),
                        'csrfToken' => csrf_token(),
                        'spoofMethod' => 'PUT',
                        'errors' => collect($errorMap)->only(['current_password', 'password', 'password_confirmation'])->all(),
                        'status' => session('status'),
                    ],
                    'removeAvatarAction' => $user->avatar ? [
                        'action' => route('profile.avatar.remove'),
                        'csrfToken' => csrf_token(),
                    ] : null,
                    'backHref' => route('dashboard'),
                ];

                return $props;
            })([
                'user' => $user,
            ]),
        );
    }

    public function update(Request $request, UploadedImageOptimizer $imageOptimizer)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|file|mimetypes:image/jpeg,image/png,image/gif,image/avif,image/webp|max:2048',
        ]);

        $user->name = $validated['name'];

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists('avatars/'.$user->avatar)) {
                Storage::disk('public')->delete('avatars/'.$user->avatar);
            }

            $storedAvatar = $imageOptimizer->store($request->file('avatar'), 'avatars', 320, 320, 84);
            $user->avatar = basename($storedAvatar['path']);
        }

        $user->save();

        return redirect()->back()
            ->with('success', 'Profil berhasil diperbarui!');
    }

    public function removeAvatar()
    {
        $user = auth()->user();

        if ($user->avatar && Storage::disk('public')->exists('avatars/'.$user->avatar)) {
            Storage::disk('public')->delete('avatars/'.$user->avatar);
        }

        $user->avatar = null;
        $user->save();

        return redirect()->back()
            ->with('success', 'Foto profil berhasil dihapus!');
    }
}
