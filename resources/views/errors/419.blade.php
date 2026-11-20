<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Session Expired - School Repository</title>
    <x-background-assets />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="login-page">
<div class="login-wrap">
    <div class="login-card glass-card" style="text-align:center">
        <img src="{{ asset('images/brand-logo.png') }}" alt="School Repository" class="login-logo">
        <h1>Session Expired</h1>
        <p class="muted">Your browser had an old or expired security token. The project now uses database-backed sessions to prevent recurring session-file permission problems.</p>
        <p class="muted"><strong>Use only http://127.0.0.1:8000</strong> and reload the login page before signing in again.</p>
        <a class="btn" href="{{ route('login') }}">Return to Login</a>
    </div>
</div>
</body>
</html>
