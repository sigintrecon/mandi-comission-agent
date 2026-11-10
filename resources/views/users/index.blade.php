<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Mandi Manager Pro</title>
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
                    @if(auth()->user()->canManageClients())<a href="{{ route('clients.index') }}" class="text-purple-200 hover:text-white">Clients</a>@endif
                    <a href="{{ route('users.index') }}" class="text-white font-semibold">Users</a>
                    @if(auth()->user()->canViewAuditLogs())<a href="{{ route('audit.index') }}" class="text-purple-200 hover:text-white">Audit Logs</a>@endif
                    <form method="POST" action="/logout">@csrf<button type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 border border-red-500/30 px-4 py-2 rounded-lg text-sm">Logout</button></form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">
        @if(session('success'))<div class="bg-green-500/20 border border-green-500/40 text-green-300 p-4 rounded-xl mb-6">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-500/20 border border-red-500/40 text-red-300 p-4 rounded-xl mb-6">{{ session('error') }}</div>@endif

        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold">User Management</h2>
            <a href="{{ route('users.create') }}" class="bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-500 hover:to-fuchsia-500 text-white font-semibold py-3 px-6 rounded-xl transition-all shadow-lg">+ Add New User</a>
        </div>

        <div class="glass-card rounded-2xl p-6 border border-white/10 w-full">
            <form method="POST" action="{{ route('users.bulk-action') }}" id="bulkForm">
                @csrf
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="selectAll" class="w-5 h-5 rounded border-purple-500/30 bg-gray-800/50 cursor-pointer">
                        <label for="selectAll" class="text-sm text-white/70 cursor-pointer">Select All</label>
                    </div>
                    <select name="action" id="bulkAction" class="bg-gray-800/50 border border-purple-500/30 rounded-lg px-4 py-2 text-sm text-white">
                        <option value="">Bulk Actions...</option>
                        <option value="activate">Activate Selected</option>
                        <option value="deactivate">Deactivate Selected</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded-lg text-sm transition-all">Apply</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-purple-200/60 text-sm border-b border-white/10"><th class="pb-3 w-12"></th><th class="pb-3 text-left">Name</th><th class="pb-3 text-left">Email</th><th class="pb-3 text-left">Role</th><th class="pb-3 text-left">Status</th><th class="pb-3 text-left">Created</th><th class="pb-3 text-right">Actions</th></tr></thead>
                        <tbody class="text-sm">
                            @foreach($users as $user)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="py-4">@if($user->id !== 1)<input type="checkbox" name="user_ids[]" value="{{ $user->id }}" form="bulkForm" class="user-checkbox w-4 h-4 rounded border-purple-500/30 bg-gray-800/50 cursor-pointer">@endif</td>
                                <td class="py-4 text-white font-medium">{{ $user->name }}@if($user->id === 1)<span class="ml-2 text-xs bg-purple-500/20 text-purple-300 px-2 py-0.5 rounded">Primary</span>@endif</td>
                                <td class="py-4 text-white/70">{{ $user->email }}</td>
                                <td class="py-4"><span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->getRoleBadgeColor() }}">{{ $user->getRoleDisplayName() }}</span></td>
                                <td class="py-4"><span class="px-3 py-1 rounded-full text-xs font-semibold @if($user->is_active) bg-green-500/20 text-green-400 @else bg-red-500/20 text-red-400 @endif">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="py-4 text-white/50">{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="py-4 text-right space-x-3">
                                    <button type="button" onclick="showPasswordModal({{ $user->id }})" class="text-yellow-400 hover:text-yellow-300 text-xs">Password</button>
                                    <a href="{{ route('users.edit', $user) }}" class="text-purple-400 hover:text-purple-300 text-xs">Edit</a>
                                    @if(auth()->user()->canDeleteUser($user))<form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE')<button type="submit" class="text-red-400 hover:text-red-300 text-xs font-semibold">Delete</button></form>@else<span class="text-gray-500 text-xs">Protected</span>@endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </div>
    </main>

    <div id="passwordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="glass-card rounded-2xl p-8 w-full max-w-md relative border border-purple-500/30">
            <button onclick="closePasswordModal()" class="absolute top-4 right-4 text-white/50 hover:text-white text-2xl leading-none">&times;</button>
            <h3 class="text-2xl font-bold text-white mb-6">Change Password</h3>
            <form id="passwordForm" method="POST" class="space-y-4">
                @csrf
                <div class="relative">
                    <label class="block text-purple-200 text-sm mb-1">New Password</label>
                    <input type="password" name="new_password" id="modal_new_password" required class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-2.5 pr-12 outline-none focus:border-purple-500">
                    <button type="button" onclick="togglePassword('modal_new_password')" class="absolute right-3 top-9 text-purple-300 hover:text-white">
                        <svg id="modal_new_password_eye_open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg id="modal_new_password_eye_closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532-7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                    </button>
                </div>
                <div class="relative">
                    <label class="block text-purple-200 text-sm mb-1">Confirm Password</label>
                    <input type="password" name="new_password_confirmation" id="modal_confirm_password" required class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-2.5 pr-12 outline-none focus:border-purple-500">
                    <button type="button" onclick="togglePassword('modal_confirm_password')" class="absolute right-3 top-9 text-purple-300 hover:text-white">
                        <svg id="modal_confirm_password_eye_open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg id="modal_confirm_password_eye_closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532-7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                    </button>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white font-bold py-3 rounded-lg transition-all mt-4">Change Password</button>
            </form>
        </div>
    </div>

    <footer class="border-t border-white/10 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center">
            <p class="text-xs"><span class="text-white/50">Security by </span><a href="https://sigintrecon.com/" target="_blank" class="text-red-400 hover:text-red-300 font-semibold">SigintRecon</a></p>
            <p class="text-white/40 text-xs">&copy; 2026 Mandi Manager Pro.</p>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var selectAll = document.getElementById('selectAll');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    document.querySelectorAll('.user-checkbox').forEach(function(cb) { cb.checked = selectAll.checked; });
                });
            }
            var bulkForm = document.getElementById('bulkForm');
            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    var action = document.getElementById('bulkAction').value;
                    var checked = document.querySelectorAll('.user-checkbox:checked');
                    if (!action) { e.preventDefault(); alert('Please select a bulk action.'); return false; }
                    if (checked.length === 0) { e.preventDefault(); alert('Please select at least one user.'); return false; }
                    if (action === 'delete' && !confirm('Delete ' + checked.length + ' user(s)?')) { e.preventDefault(); return false; }
                });
            }
        });
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
        function showPasswordModal(userId) {
            document.getElementById('passwordForm').action = '/users/' + userId + '/change-password';
            document.getElementById('passwordModal').classList.remove('hidden');
        }
        function closePasswordModal() { document.getElementById('passwordModal').classList.add('hidden'); }
    </script>
</body>
</html>