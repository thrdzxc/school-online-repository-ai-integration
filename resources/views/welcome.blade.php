<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <x-background-assets />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    <style>
        .welcome-shell{min-height:100vh;display:grid;place-items:center;padding:28px}
        .welcome-card{width:min(980px,96vw);background:var(--glass);border:1px solid rgba(255,255,255,.65);border-radius:24px;padding:38px;box-shadow:var(--shadow)}
        .welcome-grid{display:grid;grid-template-columns:1.3fr .7fr;gap:28px;align-items:center}
        .welcome-title{font-size:42px;line-height:1.03;margin:8px 0 14px;letter-spacing:-.04em}
        .welcome-copy{color:#2f416d;max-width:620px}.welcome-features{display:grid;gap:10px;margin-top:22px}
        .welcome-feature{padding:12px 14px;border-radius:12px;background:rgba(255,255,255,.24);border:1px solid rgba(255,255,255,.30)}
        .welcome-side{padding:24px;border-radius:18px;background:linear-gradient(145deg,rgba(25,49,78,.86),rgba(37,73,108,.80));color:white}
        .welcome-side p{color:#dce4ff}.welcome-actions{display:grid;gap:10px;margin-top:18px}.welcome-actions .btn{width:100%}
        .welcome-logo{display:flex;align-items:center;gap:14px}.welcome-logo img{width:80px;height:80px;padding:6px;object-fit:contain;background:rgba(248,251,255,.96);border-radius:50%;box-shadow:0 8px 24px rgba(26,50,80,.18)}
        @media(max-width:760px){.welcome-grid{grid-template-columns:1fr}.welcome-title{font-size:34px}.welcome-card{padding:24px}}
    </style>
</head>
<body class="app-page">
<div class="welcome-shell">
    <div class="welcome-card">
        <div class="welcome-grid">
            <div>
                <div class="welcome-logo">
                    <img src="{{ asset('images/brand-logo.png') }}" alt="School Repository Logo">
                    <div>
                        <span class="badge info">School Online Repository</span>
                    </div>
                </div>
                <h1 class="welcome-title">School Online Repository</h1>
                <p class="welcome-copy">A room-based learning repository where students enroll using unique section keys, communicate privately with advisers, submit activities, receive notifications, and view AI rubric recommendations together with adviser-finalized grades.</p>
                <div class="welcome-features">
                    <div class="welcome-feature"><strong>Rooms & Enrollment Keys</strong><br><span class="small muted">Separate students by section and keep activities organized per room.</span></div>
                    <div class="welcome-feature"><strong>Private Online Chat</strong><br><span class="small muted">Adviser and student conversations are connected to the correct room.</span></div>
                    <div class="welcome-feature"><strong>AI Rubric Scores</strong><br><span class="small muted">Both sides can view rubric-based AI recommendations while advisers control final grades.</span></div>
                </div>
            </div>
            <div class="welcome-side">
                <span class="feature-kicker">CHOOSE YOUR PORTAL</span>
                <h2>Sign in to continue</h2>
                <p>Use the portal that matches your account role.</p>
                <div class="welcome-actions">
                    <a class="btn secondary" href="{{ route('student.login') }}">Student Login</a>
                    <a class="btn secondary" href="{{ route('adviser.login') }}">Adviser Login</a>
                    <a class="btn secondary" href="{{ route('admin.login') }}">Administrator Login</a>
                    <a class="btn" href="{{ route('login') }}">General Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
