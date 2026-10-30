<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Code — Niralagam</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --primary: #4f46e5; --primary-dark: #4338ca; --bg: #f1f5f9; --card: #fff; --border: #e2e8f0; --text: #1e293b; --text-muted: #64748b; --danger: #dc2626; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }

        .card {
            background: var(--card);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.1);
            padding: 2.75rem 2.5rem;
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        .otp-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--primary);
        }

        h2 { font-size: 1.5rem; font-weight: 700; color: var(--text); margin-bottom: .5rem; }
        .sub { font-size: .875rem; color: var(--text-muted); margin-bottom: 2rem; line-height: 1.5; }
        .sub strong { color: var(--text); }

        /* OTP boxes */
        .otp-input-group {
            display: flex;
            gap: .625rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .otp-input-group input {
            width: 50px; height: 58px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            border: 2px solid var(--border);
            border-radius: 10px;
            outline: none;
            color: var(--text);
            background: #f8fafc;
            transition: border-color .15s, box-shadow .15s;
        }
        .otp-input-group input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.12); background: #fff; }
        .otp-input-group input.is-invalid { border-color: var(--danger); }

        .invalid-feedback { font-size: .8rem; color: var(--danger); margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; gap: .25rem; }

        .btn-verify {
            width: 100%; background: var(--primary); color: #fff; border: none;
            padding: .8rem; border-radius: 8px; font-size: .95rem; font-weight: 600;
            cursor: pointer; transition: background .15s;
        }
        .btn-verify:hover { background: var(--primary-dark); }

        .resend-row { margin-top: 1.25rem; font-size: .825rem; color: var(--text-muted); }
        .resend-row a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .resend-row a:hover { text-decoration: underline; }

        .timer { font-size: .78rem; color: var(--text-muted); margin-top: .5rem; }
        .timer span { font-weight: 600; color: var(--text); }
    </style>
</head>
<body>

<div class="card">
    <div class="otp-icon">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.41-.82a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
        </svg>
    </div>

    <h2>Check your email</h2>
    <p class="sub">We sent a 6-digit code to<br><strong>{{ $email }}</strong><br>It expires in 10 minutes.</p>

    @error('otp')
        <div class="invalid-feedback">
            <svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </div>
    @enderror

    <form method="POST" action="{{ route('otp.verify') }}" id="otpForm">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="otp" id="otpHidden">

        <div class="otp-input-group">
            @for($i = 0; $i < 6; $i++)
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    class="otp-box {{ $errors->has('otp') ? 'is-invalid' : '' }}"
                    autocomplete="off"
                >
            @endfor
        </div>

        <button type="submit" class="btn-verify">Verify Code</button>
    </form>

    <div class="resend-row">
        Didn't receive it? <a href="{{ route('login') }}">Try a different email</a>
        &nbsp;·&nbsp;
        <a href="{{ route('otp.send') }}" id="resendLink" style="pointer-events:none;opacity:.5">Resend code</a>
    </div>
    <div class="timer">Resend available in <span id="countdown">60</span>s</div>
</div>

<script>
    // OTP box auto-advance
    const boxes = document.querySelectorAll('.otp-box');
    const hidden = document.getElementById('otpHidden');
    const form   = document.getElementById('otpForm');

    boxes.forEach((box, i) => {
        box.addEventListener('input', (e) => {
            const val = e.target.value.replace(/\D/g, '');
            e.target.value = val.slice(-1);
            if (val && i < boxes.length - 1) boxes[i + 1].focus();
            syncHidden();
        });
        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !box.value && i > 0) boxes[i - 1].focus();
        });
        box.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
            text.split('').forEach((ch, j) => { if (boxes[j]) boxes[j].value = ch; });
            syncHidden();
            if (text.length === 6) form.submit();
        });
    });

    function syncHidden() {
        hidden.value = Array.from(boxes).map(b => b.value).join('');
        if (hidden.value.length === 6) {
            setTimeout(() => form.submit(), 120);
        }
    }

    // Countdown for resend
    let secs = 60;
    const cd = document.getElementById('countdown');
    const rl = document.getElementById('resendLink');
    const timer = setInterval(() => {
        secs--;
        cd.textContent = secs;
        if (secs <= 0) {
            clearInterval(timer);
            rl.style.cssText = '';
            cd.closest('.timer').textContent = '';
        }
    }, 1000);
</script>
</body>
</html>
