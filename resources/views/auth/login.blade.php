<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mandi Manager Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-animated { background: linear-gradient(-45deg, #0f0c29, #302b63, #24243e, #1a0b2e); background-size: 400% 400%; animation: gradientBG 15s ease infinite; }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(168, 85, 247, 0.4); box-shadow: 0 0 15px rgba(168, 85, 247, 0.3); }
    </style>
</head>
<body class="bg-animated min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md glass-card rounded-2xl p-8 shadow-2xl">
        <div class="text-center mb-8">
            <img src="{{ asset('images/elemeentra-logo.jpeg') }}" alt="Logo" class="h-16 w-auto mx-auto mb-4">
            <h1 class="text-2xl font-bold text-white mb-2">Super Admin Portal</h1>
            <p class="text-purple-200/70 text-sm">Welcome to Mandi Manager Pro</p>
        </div>

        <form method="POST" action="/login" class="space-y-6">
            @csrf
            <div>
                <label class="block text-purple-200 text-sm font-medium mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500">
            </div>
            <div class="relative">
                <label class="block text-purple-200 text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" id="login_password" required class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 pr-12 outline-none focus:border-purple-500">
                <button type="button" onclick="togglePassword('login_password')" class="absolute right-3 top-9 text-purple-300 hover:text-white focus:outline-none">
                    <svg id="login_password_eye_open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <svg id="login_password_eye_closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532-7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                </button>
            </div>
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/30 text-red-300 p-3 rounded-lg text-sm text-center">{{ $errors->first() }}</div>
            @endif
            <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-500 hover:to-fuchsia-500 text-white font-bold py-3.5 rounded-lg transition-all shadow-lg">Login</button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-xs"><span class="text-white/50">Security by </span><a href="https://sigintrecon.com/" target="_blank" class="text-red-400 hover:text-red-300 font-semibold">SigintRecon</a></p>
        </div>
    </div>
    <script>
        function togglePassword(id) {
            var input = document.getElementById(id);
            var openEye = document.getElementById(id + '_eye_open');
            var closedEye = document.getElementById(id + '_eye_closed');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                if (openEye) openEye.classList.add('hidden');
                if (closedEye) closedEye.classList.remove('hidden');
            } else {
                input.type = 'password';
                if (openEye) openEye.classList.remove('hidden');
                if (closedEye) closedEye.classList.add('hidden');
            }
        }
    </script>
</body>
</html>