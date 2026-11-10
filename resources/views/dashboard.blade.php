<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &mdash; Mandi Manager Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
                    <a href="{{ route('dashboard') }}" class="text-white font-semibold">Dashboard</a>
                    @if(auth()->user()->canManageClients())<a href="{{ route('clients.index') }}" class="text-purple-200 hover:text-white">Clients</a>@endif
                    @if(auth()->user()->canManageUsers())<a href="{{ route('users.index') }}" class="text-purple-200 hover:text-white">Users</a>@endif
                    @if(auth()->user()->canViewAuditLogs())<a href="{{ route('audit.index') }}" class="text-purple-200 hover:text-white">Audit Logs</a>@endif
                    <form method="POST" action="/logout">@csrf<button type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 border border-red-500/30 px-4 py-2 rounded-lg text-sm">Logout</button></form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">
        <div class="mb-8">
            <h2 class="text-3xl font-bold mb-2">Welcome to Mandi Manager Pro</h2>
            <p class="text-purple-200/70">Super Admin Dashboard &mdash; Manage clients and users from one place</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-card rounded-2xl p-6 border border-purple-500/30">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-white/70 text-sm">Total Clients</h3>
                    <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-purple-400">{{ $totalClients ?? 0 }}</p>
                <p class="text-xs text-white/50 mt-2">Active clients</p>
            </div>
            <div class="glass-card rounded-2xl p-6 border border-green-500/30">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-white/70 text-sm">Active Clients</h3>
                    <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-green-400">{{ $activeClients ?? 0 }}</p>
                <p class="text-xs text-white/50 mt-2">Currently active</p>
            </div>
            <div class="glass-card rounded-2xl p-6 border border-yellow-500/30">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-white/70 text-sm">Pending Setup</h3>
                    <div class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-yellow-400">{{ $pendingClients ?? 0 }}</p>
                <p class="text-xs text-white/50 mt-2">Awaiting setup</p>
            </div>
            <div class="glass-card rounded-2xl p-6 border border-blue-500/30">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-white/70 text-sm">Total Users</h3>
                    <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-blue-400">{{ $totalUsers ?? 1 }}</p>
                <p class="text-xs text-white/50 mt-2">System users</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="glass-card rounded-2xl p-6 border border-white/10">
                <h3 class="text-xl font-bold mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    @if(auth()->user()->canManageClients())
                        <a href="{{ route('clients.create') }}" class="block w-full bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-500 hover:to-fuchsia-500 text-white font-semibold py-3 px-4 rounded-lg transition-all text-center">+ Add New Client</a>
                        <a href="{{ route('clients.index') }}" class="block w-full bg-gray-700/50 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition-all text-center">Manage Clients</a>
                    @endif
                    @if(auth()->user()->canManageUsers())
                        <a href="{{ route('users.index') }}" class="block w-full bg-gray-700/50 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition-all text-center">Manage Users</a>
                    @endif
                </div>
            </div>
            <div class="glass-card rounded-2xl p-6 border border-white/10">
                <h3 class="text-xl font-bold mb-4">System Status</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-green-500/10 border border-green-500/30 rounded-lg"><span class="text-green-300">System Status</span><span class="text-green-400 font-semibold">Operational</span></div>
                    <div class="flex items-center justify-between p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg"><span class="text-blue-300">Database</span><span class="text-blue-400 font-semibold">Connected</span></div>
                    <div class="flex items-center justify-between p-3 bg-purple-500/10 border border-purple-500/30 rounded-lg"><span class="text-purple-300">Security</span><span class="text-purple-400 font-semibold">Active</span></div>
                </div>
            </div>
        </div>
    </main>

    <footer class="border-t border-white/10 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center">
            <p class="text-xs"><span class="text-white/50">Security by </span><a href="https://sigintrecon.com/" target="_blank" class="text-red-400 hover:text-red-300 font-semibold">SigintRecon</a></p>
            <p class="text-white/40 text-xs">&copy; 2026 Mandi Manager Pro. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>