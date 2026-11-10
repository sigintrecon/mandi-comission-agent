<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Mandi Manager Pro</title>
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
                <div class="flex items-center gap-4"><a href="{{ route('dashboard') }}" class="text-purple-200 hover:text-white font-semibold">Dashboard</a>
                    <a href="{{ route('clients.index') }}" class="text-purple-200 hover:text-white">Clients</a>
                    <a href="{{ route('users.index') }}" class="text-purple-200 hover:text-white">Users</a>
                    <a href="{{ route('audit.index') }}" class="text-white font-semibold">Audit Logs</a>
                    <form method="POST" action="/logout">@csrf<button type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 border border-red-500/30 px-4 py-2 rounded-lg text-sm">Logout</button></form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold">Audit Logs</h2>
                <p class="text-purple-200/70 text-sm mt-1">Complete activity trail of all users</p>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 border border-white/10 mb-6">
            <form method="GET" action="{{ route('audit.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-purple-200 text-xs mb-1">User</label>
                    <select name="user_id" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Users</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-purple-200 text-xs mb-1">Action</label>
                    <select name="action" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Actions</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                        <option value="logged_in" {{ request('action') == 'logged_in' ? 'selected' : '' }}>Logged In</option>
                        <option value="logged_out" {{ request('action') == 'logged_out' ? 'selected' : '' }}>Logged Out</option>
                        <option value="password_changed" {{ request('action') == 'password_changed' ? 'selected' : '' }}>Password Changed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-purple-200 text-xs mb-1">Entity Type</label>
                    <select name="entity_type" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Types</option>
                        <option value="User" {{ request('entity_type') == 'User' ? 'selected' : '' }}>User</option>
                        <option value="Client" {{ request('entity_type') == 'Client' ? 'selected' : '' }}>Client</option>
                    </select>
                </div>
                <div>
                    <label class="block text-purple-200 text-xs mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-purple-200 text-xs mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-5 flex gap-2">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white px-6 py-2 rounded-lg text-sm transition-all">Apply Filters</button>
                    <a href="{{ route('audit.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg text-sm transition-all">Clear</a>
                </div>
            </form>
        </div>

        <div class="glass-card rounded-2xl p-6 border border-white/10">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-purple-200/60 text-sm border-b border-white/10">
                            <th class="pb-3 text-left">Timestamp</th>
                            <th class="pb-3 text-left">User</th>
                            <th class="pb-3 text-left">Action</th>
                            <th class="pb-3 text-left">Entity</th>
                            <th class="pb-3 text-left">IP Address</th>
                            <th class="pb-3 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($auditLogs as $log)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="py-4 text-white/70 text-xs">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="py-4 text-white font-medium">
                                {{ $log->user ? $log->user->name : 'System' }}
                                @if($log->user)
                                    <span class="ml-2 text-xs {{ $log->user->getRoleBadgeColor() }} px-2 py-0.5 rounded">{{ $log->user->getRoleDisplayName() }}</span>
                                @endif
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                    @if($log->action === 'created') bg-green-500/20 text-green-400
                                    @elseif($log->action === 'updated') bg-blue-500/20 text-blue-400
                                    @elseif($log->action === 'deleted') bg-red-500/20 text-red-400
                                    @elseif($log->action === 'logged_in') bg-purple-500/20 text-purple-400
                                    @elseif($log->action === 'logged_out') bg-gray-500/20 text-gray-400
                                    @else bg-yellow-500/20 text-yellow-400 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td class="py-4 text-purple-300 text-xs">{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                            <td class="py-4 text-white/50 text-xs font-mono">{{ $log->ip_address }}</td>
                            <td class="py-4 text-right">
                                <button onclick="showDetails({{ $log->id }})" class="text-purple-400 hover:text-purple-300 text-xs">View</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-10 text-center text-white/50">No audit logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($auditLogs->hasPages())<div class="mt-6">{{ $auditLogs->links() }}</div>@endif
        </div>
    </main>

    <div id="detailsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="glass-card rounded-2xl p-8 w-full max-w-2xl relative border border-purple-500/30 max-h-[80vh] overflow-y-auto">
            <button onclick="closeDetailsModal()" class="absolute top-4 right-4 text-white/50 hover:text-white text-2xl leading-none">&times;</button>
            <h3 class="text-2xl font-bold text-white mb-6">Log Details</h3>
            <div id="detailsContent" class="space-y-4 text-sm"></div>
        </div>
    </div>

    <footer class="border-t border-white/10 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center">
            <p class="text-xs"><span class="text-white/50">Security by </span><a href="https://sigintrecon.com/" target="_blank" class="text-red-400 hover:text-red-300 font-semibold">SigintRecon</a></p>
            <p class="text-white/40 text-xs">&copy; 2026 Mandi Manager Pro. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const auditData = @json($auditLogs->items());
        function showDetails(logId) {
            const log = auditData.find(l => l.id === logId);
            if (!log) return;
            const content = document.getElementById('detailsContent');
            content.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-800/50 p-3 rounded-lg"><label class="text-purple-200 text-xs">Timestamp</label><p class="text-white">${log.created_at}</p></div>
                    <div class="bg-gray-800/50 p-3 rounded-lg"><label class="text-purple-200 text-xs">User</label><p class="text-white">${log.user ? log.user.name : 'System'}</p></div>
                    <div class="bg-gray-800/50 p-3 rounded-lg"><label class="text-purple-200 text-xs">Action</label><p class="text-white">${log.action}</p></div>
                    <div class="bg-gray-800/50 p-3 rounded-lg"><label class="text-purple-200 text-xs">Entity</label><p class="text-white">${log.entity_type} #${log.entity_id}</p></div>
                    <div class="bg-gray-800/50 p-3 rounded-lg col-span-2"><label class="text-purple-200 text-xs">IP Address</label><p class="text-white font-mono">${log.ip_address || 'N/A'}</p></div>
                    <div class="bg-gray-800/50 p-3 rounded-lg col-span-2"><label class="text-purple-200 text-xs">User Agent</label><p class="text-white text-xs">${log.user_agent || 'N/A'}</p></div>
                    ${log.old_values ? `<div class="bg-red-500/10 border border-red-500/30 p-3 rounded-lg col-span-2"><label class="text-red-300 text-xs">Old Values</label><pre class="text-white text-xs mt-1 overflow-x-auto">${JSON.stringify(log.old_values, null, 2)}</pre></div>` : ''}
                    ${log.new_values ? `<div class="bg-green-500/10 border border-green-500/30 p-3 rounded-lg col-span-2"><label class="text-green-300 text-xs">New Values</label><pre class="text-white text-xs mt-1 overflow-x-auto">${JSON.stringify(log.new_values, null, 2)}</pre></div>` : ''}
                </div>
            `;
            document.getElementById('detailsModal').classList.remove('hidden');
        }
        function closeDetailsModal() { document.getElementById('detailsModal').classList.add('hidden'); }
    </script>
</body>
</html>