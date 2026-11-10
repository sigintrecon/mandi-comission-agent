<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Mandi Manager Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-animated { background: linear-gradient(-45deg, #0f0c29, #302b63, #24243e, #1a0b2e); background-size: 400% 400%; animation: gradientBG 15s ease infinite; }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="bg-animated min-h-screen text-white flex flex-col">
    <nav class="glass-card border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/elemeentra-logo.jpeg') }}" alt="Logo" class="h-10 w-auto">
                    <div><h1 class="text-xl font-bold">Mandi Manager Pro</h1><p class="text-xs text-purple-200/70">Super Admin Portal</p></div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="text-purple-200 hover:text-white font-semibold">Dashboard</a>
                    <a href="{{ route('users.index') }}" class="text-purple-200 hover:text-white">&larr; Back to Users</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">
        <div class="glass-card rounded-2xl p-8 border border-white/10">
            <h2 class="text-3xl font-bold mb-6">Add New User</h2>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-purple-200 text-sm mb-1">Full Name *</label>
                        <input type="text" name="name" required value="{{ old('name') }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500">
                        @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-purple-200 text-sm mb-1">Email Address *</label>
                        <input type="email" name="email" required value="{{ old('email') }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500">
                        @error('email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="relative">
                        <label class="block text-purple-200 text-sm mb-1">Password *</label>
                        <input type="password" name="password" id="user_create_password" required class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 pr-12 outline-none focus:border-purple-500">
                        <button type="button" onclick="togglePassword('user_create_password')" class="absolute right-3 top-9 text-purple-300 hover:text-white">
                            <svg id="user_create_password_eye_open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg id="user_create_password_eye_closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532-7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </button>
                        @error('password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Role *</label>
                        <select name="role" required class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500">
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="client_manager" {{ old('role') == 'client_manager' ? 'selected' : '' }}>Client Manager</option>
                            <option value="viewer" {{ old('role') == 'viewer' ? 'selected' : '' }}>Viewer</option>
                        </select>
                        <p class="mt-1 text-xs text-white/50"><strong>Admin:</strong> Full access | <strong>Client Manager:</strong> Clients only | <strong>Viewer:</strong> Read-only</p>
                        @error('role') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-5 h-5 rounded border-purple-500/30 bg-gray-800/50">
                            <span class="text-purple-200">Active Account</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-4 mt-8">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-500 hover:to-fuchsia-500 text-white font-bold py-3.5 rounded-lg transition-all shadow-lg">Create User</button>
                    <a href="{{ route('users.index') }}" class="px-8 py-3.5 border border-white/20 rounded-lg text-white hover:bg-white/10 transition-all text-center">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="border-t border-white/10 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center">
            <p class="text-xs"><span class="text-white/50">Security by </span><a href="https://sigintrecon.com/" target="_blank" class="text-red-400 hover:text-red-300 font-semibold">SigintRecon</a></p>
            <p class="text-white/40 text-xs">&copy; 2026 Mandi Manager Pro.</p>
        </div>
    </footer>
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