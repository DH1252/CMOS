<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $errors = session('errors');

        return \Inertia\Inertia::render(
            'LoginPage',
            [
                'appName' => Setting::get('app_name', 'CMOS'),
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

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        ActivityLog::log('logout', 'User logged out');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
