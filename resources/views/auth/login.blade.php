<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Niralagam</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:      #4f46e5;
            --primary-dark: #4338ca;
            --bg:           #f8fafc;
            --card:         #ffffff;
            --border:       #e2e8f0;
            --text:         #0d1117;
            --muted:        #64748b;
            --danger:       #be123c;
            --danger-bg:    #fff1f2;
            --danger-bdr:   #fecdd3;
        }

        html, body { height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: minmax(100vh, 1fr);
        }
        @media (max-width: 860px) {
            body { grid-template-columns: 1fr; }
            .brand-panel { display: none; }
        }

        /* ════════════════════════════════
           LEFT BRAND PANEL
        ════════════════════════════════ */
        .brand-panel {
            position: relative;
            background: linear-gradient(150deg, #06041a 0%, #110c2e 40%, #0d1023 75%, #080c18 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 3.5rem;
            overflow: hidden;
        }

        /* Grid texture */
        .brand-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,.045) 1px, transparent 1px);
            background-size: 52px 52px;
            pointer-events: none;
        }

        /* Ambient glow blobs */
        .brand-glow {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }
        .brand-glow-a {
            width: 560px;
            height: 560px;
            top: -200px;
            right: -220px;
            background: radial-gradient(circle, rgba(99,102,241,.2) 0%, transparent 60%);
        }
        .brand-glow-b {
            width: 420px;
            height: 420px;
            bottom: -160px;
            left: -140px;
            background: radial-gradient(circle, rgba(168,85,247,.15) 0%, transparent 60%);
        }
        .brand-glow-c {
            width: 220px;
            height: 220px;
            top: 48%;
            left: 40%;
            transform: translate(-50%, -50%);
            background: radial-gradient(circle, rgba(56,189,248,.06) 0%, transparent 65%);
        }

        /* Noise overlay for depth */
        .brand-noise {
            position: absolute;
            inset: 0;
            opacity: .025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 200px 200px;
            pointer-events: none;
        }

        /* ── Brand top — logo ── */
        .brand-top {
            position: relative;
            z-index: 1;
        }
        .brand-logo-pill {
            display: inline-flex;
            align-items: center;
            gap: .625rem;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 40px;
            padding: .45rem .9rem .45rem .45rem;
            backdrop-filter: blur(6px);
        }
        .brand-logo-img-wrap {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            padding: 3px;
        }
        .brand-logo-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            border-radius: 50%;
        }
        .brand-logo-name {
            font-size: .82rem;
            font-weight: 700;
            color: rgba(255,255,255,.88);
            letter-spacing: .01em;
        }
        .brand-logo-tamil {
            font-size: .7rem;
            color: rgba(255,255,255,.35);
            margin-left: .25rem;
        }

        /* ── Brand body ── */
        .brand-body {
            position: relative;
            z-index: 1;
        }
        .brand-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #a5b4fc;
            background: rgba(99,102,241,.1);
            border: 1px solid rgba(99,102,241,.22);
            padding: .3rem .875rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }
        .brand-chip-dot {
            width: 5px;
            height: 5px;
            background: #818cf8;
            border-radius: 50%;
            box-shadow: 0 0 6px #818cf8, 0 0 12px rgba(129,140,248,.4);
            animation: chipPulse 2.4s ease-in-out infinite;
        }
        @keyframes chipPulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 6px #818cf8, 0 0 12px rgba(129,140,248,.4); }
            50%       { opacity: .6; box-shadow: 0 0 3px #818cf8; }
        }

        .brand-headline {
            font-size: clamp(2.6rem, 3.5vw, 3.5rem);
            font-weight: 900;
            color: #fff;
            line-height: 1.05;
            letter-spacing: -.04em;
            margin-bottom: 1.25rem;
        }
        .brand-headline em {
            font-style: italic;
            background: linear-gradient(92deg, #818cf8 0%, #c084fc 55%, #e879f9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-sub {
            font-size: .9rem;
            color: rgba(255,255,255,.42);
            line-height: 1.78;
            max-width: 330px;
            margin-bottom: 2.75rem;
        }

        /* Feature list */
        .brand-features {
            display: flex;
            flex-direction: column;
            gap: .9rem;
        }
        .brand-feat {
            display: flex;
            align-items: center;
            gap: .875rem;
            font-size: .86rem;
            color: rgba(255,255,255,.62);
            line-height: 1.4;
        }
        .feat-check {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(99,102,241,.2) 0%, rgba(168,85,247,.15) 100%);
            border: 1px solid rgba(129,140,248,.28);
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a5b4fc;
        }

        /* ── Brand footer ── */
        .brand-foot-wrap {
            position: relative;
            z-index: 1;
        }
        .brand-foot-line {
            height: 1px;
            background: linear-gradient(90deg, rgba(255,255,255,.07) 0%, rgba(255,255,255,.02) 100%);
            margin-bottom: 1.25rem;
        }
        .brand-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand-foot-left {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .73rem;
            color: rgba(255,255,255,.22);
        }
        .brand-foot-sep { opacity: .5; }
        .brand-foot-tamil { color: rgba(255,255,255,.15); }
        .brand-foot-pill {
            font-size: .65rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(129,140,248,.6);
            background: rgba(99,102,241,.08);
            border: 1px solid rgba(99,102,241,.14);
            padding: .2rem .6rem;
            border-radius: 20px;
        }

        /* ════════════════════════════════
           RIGHT FORM PANEL
        ════════════════════════════════ */
        .form-panel {
            background: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 22px 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
        }

        .form-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 2.75rem 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
            animation: cardIn .4s cubic-bezier(.22,1,.36,1) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-logo { margin-bottom: 1.75rem; }
        .form-logo img { height: 36px; width: auto; display: block; }

        .form-head { margin-bottom: 1.75rem; }
        .form-head h2 {
            font-size: 1.55rem; font-weight: 800; color: var(--text);
            letter-spacing: -.02em; margin-bottom: .3rem;
        }
        .form-head p { font-size: .875rem; color: var(--muted); }

        /* error */
        .alert-error {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: var(--danger-bg);
            border: 1px solid var(--danger-bdr);
            color: var(--danger);
            padding: .75rem 1rem;
            border-radius: 10px;
            font-size: .85rem;
            margin-bottom: 1.25rem;
        }

        /* Google */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            width: 100%;
            padding: .8rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: #fff;
            font-size: .9rem;
            font-weight: 500;
            color: var(--text);
            text-decoration: none;
            transition: background .15s, border-color .15s, box-shadow .15s;
        }
        .btn-google:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        /* divider */
        .divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1.25rem 0;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .divider span { font-size: .75rem; color: var(--muted); white-space: nowrap; }

        /* form fields */
        .field { margin-bottom: 1.125rem; }
        .field-label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: .055em;
            margin-bottom: .4rem;
        }
        .input-wrap { position: relative; }
        .input-wrap svg {
            position: absolute;
            left: .875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }
        .field-input {
            width: 100%;
            padding: .75rem .875rem .75rem 2.625rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: .9rem;
            color: var(--text);
            background: #f8fafc;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .field-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3.5px rgba(79,70,229,.1);
            background: #fff;
        }
        .field-input::placeholder { color: #cbd5e1; }
        .field-input.is-invalid { border-color: var(--danger); }
        .field-error {
            display: flex;
            align-items: center;
            gap: .3rem;
            font-size: .78rem;
            color: var(--danger);
            margin-top: .35rem;
        }

        /* submit */
        .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .625rem;
            width: 100%;
            background: #0d1117;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .85rem 1.5rem;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: .375rem;
            transition: background .18s, transform .18s, box-shadow .18s;
        }
        .btn-submit:hover {
            background: #1e293b;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(13,17,23,.22);
        }
        .btn-submit:active { transform: translateY(0); }

        .form-foot {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .78rem;
            color: var(--muted);
        }
    </style>
</head>
<body>

{{-- ── Left brand panel ──────────────────────────────────── --}}
<div class="brand-panel" aria-hidden="true">

    {{-- Background layers --}}
    <div class="brand-grid"></div>
    <div class="brand-glow brand-glow-a"></div>
    <div class="brand-glow brand-glow-b"></div>
    <div class="brand-glow brand-glow-c"></div>
    <div class="brand-noise"></div>

    {{-- Logo pill --}}
    <div class="brand-top">
        <div class="brand-logo-pill">
            <div class="brand-logo-img-wrap">
                <img src="{{ asset('niralagam_logo.png') }}" alt="Niralagam">
            </div>
            <span class="brand-logo-name">Niralagam <span class="brand-logo-tamil">நிரலகம்</span></span>
        </div>
    </div>

    {{-- Headline + features --}}
    <div class="brand-body">
        <div class="brand-chip">
            <span class="brand-chip-dot"></span>
            Admin Panel
        </div>
        <h1 class="brand-headline">
            Craft&nbsp;stories<br>
            <em>that&nbsp;inspire.</em>
        </h1>
        <p class="brand-sub">
            One dashboard for all your content — write, publish, and grow your audience.
        </p>
        <div class="brand-features">
            <div class="brand-feat">
                <span class="feat-check">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1.5,6 4.5,9 10.5,3"/>
                    </svg>
                </span>
                Rich post editor with media uploads
            </div>
            <div class="brand-feat">
                <span class="feat-check">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1.5,6 4.5,9 10.5,3"/>
                    </svg>
                </span>
                Multi-tenant workspace management
            </div>
            <div class="brand-feat">
                <span class="feat-check">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1.5,6 4.5,9 10.5,3"/>
                    </svg>
                </span>
                Real-time analytics &amp; subscribers
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="brand-foot-wrap">
        <div class="brand-foot-line"></div>
        <div class="brand-foot">
            <div class="brand-foot-left">
                <span>Niralagam</span>
                <span class="brand-foot-sep">·</span>
                <span class="brand-foot-tamil">நிரலகம்</span>
                <span class="brand-foot-sep">·</span>
                <span>Blog Platform</span>
            </div>
            <span class="brand-foot-pill">v2</span>
        </div>
    </div>

</div>

{{-- ── Right form panel ──────────────────────────────────── --}}
<div class="form-panel">
    <div class="form-card">

        <div class="form-logo">
            <img src="{{ asset('niralagam_logo.png') }}" alt="Niralagam">
        </div>

        <div class="form-head">
            <h2>Welcome back</h2>
            <p>Sign in to your admin workspace</p>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Google OAuth --}}
        <a href="{{ route('auth.google') }}" class="btn-google">
            <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </a>

        <div class="divider"><span>or sign in with email OTP</span></div>

        {{-- OTP form --}}
        <form method="POST" action="{{ route('otp.send') }}">
            @csrf
            <div class="field">
                <label for="email" class="field-label">Email address</label>
                <div class="input-wrap">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        autocomplete="email"
                        autofocus
                        class="field-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        required
                    >
                </div>
                @error('email')
                    <div class="field-error">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn-submit">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                Send Login Code
            </button>
        </form>

        <div class="form-foot">Niralagam &mdash; Multi-tenant Blog Platform</div>
    </div>
</div>

</body>
</html>
