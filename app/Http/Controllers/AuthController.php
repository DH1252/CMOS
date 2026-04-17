<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\PostHogService;
use App\Support\ThemePalette;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $errors = session('errors');

        return \Inertia\Inertia::render(
            'LoginPage',
            [
                'appName' => Setting::get('app_name', 'CMOS'),
                ...$this->themePayload(),
                'loginUrl' => route('login.submit'),
                'homeUrl' => route('home'),
                'csrfToken' => csrf_token(),
                'email' => old('email', ''),
                'alertMessage' => session('error') ?? session('status') ?? '',
                'alertType' => session()->has('error') ? 'error' : (session()->has('status') ? 'info' : ''),
                'emailError' => session('errors')?->first('email') ?? '',
                'passwordError' => session('errors')?->first('password') ?? '',
                'remember' => (bool) old('remember', false),
            ],
        );
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            ActivityLog::log('login', 'User logged in');

            $user = Auth::user();
            $posthog = app(PostHogService::class);

            $posthog->identify((string) $user->id, [
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role?->name,
                'department' => $user->department?->name,
            ]);

            $posthog->capture((string) $user->id, 'user_logged_in', [
                'role' => $user->role?->name,
                'department' => $user->department?->name,
            ]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        ActivityLog::log('logout', 'User logged out');

        app(PostHogService::class)->capture((string) $user->id, 'user_logged_out');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * @return array{themeColor: string, themeVariables: array<string, string>}
     */
    private function themePayload(): array
    {
        $settings = Setting::query()
            ->whereIn('key', array_merge(['theme_color'], ThemePalette::settingKeys()))
            ->pluck('value', 'key')
            ->all();
        $theme = ThemePalette::payloadFromSettings($settings);

        return [
            'themeColor' => $theme['color'],
            'themeVariables' => $theme['variables'],
        ];
    }
}
