<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ $client->name }}</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem; background: #0a1628; overflow: hidden;
        }
        .bg-texture {
            position: fixed; inset: 0; z-index: 0;
            background:
                linear-gradient(rgba(14,165,233,0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(14,165,233,0.07) 1px, transparent 1px),
                linear-gradient(135deg, #0a1628 0%, #0f2347 40%, #0a1f3a 70%, #071525 100%);
            background-size: 50px 50px, 50px 50px, 100% 100%;
            animation: bgPulse 8s ease-in-out infinite;
        }
        @keyframes bgPulse { 0%,100%{opacity:1} 50%{opacity:0.85} }
        .orb { position:fixed; border-radius:50%; filter:blur(80px); pointer-events:none; z-index:0; }
        .orb-1 { width:400px;height:400px;top:-100px;left:-100px; background:radial-gradient(circle,rgba(14,165,233,0.25),transparent 70%); animation:float1 10s ease-in-out infinite; }
        .orb-2 { width:300px;height:300px;bottom:-80px;right:-80px; background:radial-gradient(circle,rgba(59,130,246,0.2),transparent 70%); animation:float2 12s ease-in-out infinite; }
        .orb-3 { width:200px;height:200px;top:50%;left:50%;transform:translate(-50%,-50%); background:radial-gradient(circle,rgba(99,179,237,0.1),transparent 70%); animation:float3 8s ease-in-out infinite; }
        @keyframes float1 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(30px,20px)} }
        @keyframes float2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(-20px,-30px)} }
        @keyframes float3 { 0%,100%{transform:translate(-50%,-50%) scale(1)} 50%{transform:translate(-50%,-50%) scale(1.2)} }
        .card {
            position:relative; z-index:1;
            width:100%; max-width:440px;
            background:rgba(10,25,50,0.78);
            backdrop-filter:blur(16px);
            border:1px solid rgba(14,165,233,0.3);
            border-radius:20px; padding:2.5rem 2rem;
            box-shadow: 0 0 0 1px rgba(14,165,233,0.1), 0 25px 60px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.05);
            animation:slideUp 0.5s ease-out;
        }
        @keyframes slideUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        .logo-wrap { text-align:center; margin-bottom:1.5rem; }
        .logo-wrap img { display:block;width:72px;height:72px;object-fit:contain;margin:0 auto 1rem;border-radius:50%; filter:drop-shadow(0 0 12px rgba(14,165,233,0.4)); }
        .firm-name { font-family:'Noto Nastaliq Urdu','Jameel Noori Nastaleeq',serif; font-size:1.5rem; font-weight:700; color:#fff; line-height:1.6; direction:rtl; text-shadow:0 0 20px rgba(14,165,233,0.4); }
        .firm-subtitle { font-size:0.75rem; color:rgba(147,210,255,0.6); margin-top:0.25rem; direction:ltr; }
        .divider { height:1px; background:linear-gradient(90deg,transparent,rgba(14,165,233,0.4),transparent); margin:1.25rem 0; }
        .field-label { display:block; font-size:0.8rem; font-weight:600; color:rgba(147,210,255,0.8); margin-bottom:0.4rem; text-align:left; direction:ltr; }
        .field-wrap { position:relative; margin-bottom:1.25rem; }
        .input-wrap { position:relative; }
        .field-wrap input {
            width:100%;
            padding:0.75rem 2.75rem 0.75rem 2.75rem;
            background:rgba(14,42,80,0.6);
            border:1.5px solid rgba(14,165,233,0.25);
            border-radius:10px; color:#e0f0ff; font-size:0.95rem;
            outline:none; transition:border-color 0.2s,box-shadow 0.2s; direction:ltr;
        }
        .field-wrap input:focus { border-color:rgba(14,165,233,0.7); box-shadow:0 0 0 3px rgba(14,165,233,0.12); }
        .field-wrap input::placeholder { color:rgba(147,210,255,0.35); }
        .icon-left { position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); color:rgba(14,165,233,0.55); display:flex; align-items:center; pointer-events:none; }
        .toggle-eye { position:absolute; right:0.85rem; top:50%; transform:translateY(-50%); cursor:pointer; color:rgba(14,165,233,0.55); background:none; border:none; padding:0; display:flex; align-items:center; transition:color 0.2s; }
        .toggle-eye:hover { color:rgba(14,165,233,0.95); }
        .btn-login {
            width:100%; padding:0.85rem;
            background:linear-gradient(135deg,#0ea5e9 0%,#2563eb 100%);
            color:#fff; border:none; border-radius:10px; font-size:1rem; font-weight:700;
            cursor:pointer; transition:all 0.2s; margin-top:0.5rem;
            box-shadow:0 4px 20px rgba(14,165,233,0.35); letter-spacing:0.03em;
            display:flex; align-items:center; justify-content:center; gap:0.5rem;
        }
        .btn-login:hover { transform:translateY(-1px); box-shadow:0 8px 30px rgba(14,165,233,0.5); }
        .btn-login:active { transform:translateY(0); }
        .error-box { background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; padding:0.65rem 1rem; border-radius:8px; font-size:0.85rem; margin-bottom:1rem; text-align:center; }
        .status-notice { background:rgba(234,179,8,0.1); border:1px solid rgba(234,179,8,0.3); color:#fde047; padding:0.65rem 1rem; border-radius:8px; font-size:0.85rem; margin-bottom:1rem; text-align:center; }
        .footer { text-align:center; margin-top:1.5rem; padding-top:1.25rem; border-top:1px solid rgba(14,165,233,0.12); font-size:0.72rem; color:rgba(147,210,255,0.4); }
        .footer a { color:#ef4444; font-weight:700; text-decoration:none; transition:color 0.2s; }
        .footer a:hover { color:#f87171; text-decoration:underline; }
    </style>
</head>
<body>
    <div class="bg-texture"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="card">
        <div class="logo-wrap">
            <img src="{{ asset('images/elemeentra-logo.jpeg') }}" alt="Logo">
            <div class="firm-name">{{ $client->name }}</div>
            @if($client->proprietor_name)
                <div class="firm-subtitle">{{ $client->proprietor_name }}</div>
            @endif
            @if($client->shop_number || $client->city)
                <div class="firm-subtitle">
                    @if($client->shop_number) دکان {{ $client->shop_number }} @endif
                    @if($client->city) &mdash; {{ $client->city }} @endif
                </div>
            @endif
        </div>

        <div class="divider"></div>

        @if($client->status !== 'active')
            <div class="status-notice">
                <svg style="display:inline;vertical-align:middle;margin-left:4px" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                یہ اکاؤنٹ فعال نہیں ہے۔ ایڈمن سے رابطہ کریں۔
            </div>
        @endif

        @if($errors->any())
            <div class="error-box">
                <svg style="display:inline;vertical-align:middle;margin-left:4px" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                {{ $errors->first('login') }}
            </div>
        @endif

        <form method="POST" action="/{{ $client->slug }}/login">
            @csrf
            <div class="field-wrap">
                <label class="field-label" for="login_input">Username or Email</label>
                <div class="input-wrap">
                    <span class="icon-left">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </span>
                    <input
                        type="text"
                        id="login_input"
                        name="login"
                        value="{{ old('login') }}"
                        required autofocus
                        autocomplete="username"
                        placeholder="username or email"
                    >
                </div>
            </div>

            <div class="field-wrap">
                <label class="field-label" for="password_input">Password</label>
                <div class="input-wrap">
                    <span class="icon-left">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    </span>
                    <input
                        type="password"
                        id="password_input"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="password"
                    >
                    <button type="button" class="toggle-eye" id="eyeBtn" onclick="togglePw()" title="Show/hide">
                        <svg id="eye-open" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <svg id="eye-closed" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                Log In
            </button>
        </form>

        <div class="footer">
            Security By
            <a href="https://sigintrecon.com/" target="_blank" rel="noopener">SigintRecon</a>
            &nbsp;
            <svg style="display:inline;vertical-align:middle" width="12" height="12" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
        </div>
    </div>

    <script>
        function togglePw() {
            const inp = document.getElementById('password_input');
            const open = document.getElementById('eye-open');
            const closed = document.getElementById('eye-closed');
            const isHidden = inp.type === 'password';
            inp.type = isHidden ? 'text' : 'password';
            open.style.display = isHidden ? 'none' : '';
            closed.style.display = isHidden ? '' : 'none';
        }
    </script>
</body>
</html>