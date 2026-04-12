@php
    $appName = \App\Models\Setting::get('app_name', 'CMOS');
    $themeColor = \App\Models\Setting::get('theme_color', 'purple');
    $alertMessage = session('error') ?? session('status') ?? '';
    $alertType = session()->has('error') ? 'error' : (session()->has('status') ? 'info' : '');
    $emailError = $errors->first('email');
    $passwordError = $errors->first('password');
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ $appName }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div
        id="svelte-login-root"
        data-app-name="{{ $appName }}"
        data-theme-color="{{ $themeColor }}"
        data-login-url="{{ route('login.submit') }}"
        data-home-url="{{ route('home') }}"
        data-csrf-token="{{ csrf_token() }}"
        data-email="{{ old('email') }}"
        data-alert-message="{{ $alertMessage }}"
        data-alert-type="{{ $alertType }}"
        data-email-error="{{ $emailError }}"
        data-password-error="{{ $passwordError }}"
        data-remember="{{ old('remember') ? '1' : '0' }}"
    ></div>
</body>
</html>
