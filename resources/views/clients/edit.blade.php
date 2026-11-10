<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client - Mandi Manager Pro</title>
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
                    <a href="{{ route('clients.index') }}" class="text-purple-200 hover:text-white">&larr; Back to Clients</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">
        <div class="glass-card rounded-2xl p-8 border border-white/10">
            <h2 class="text-3xl font-bold mb-6">Edit Client</h2>
            <form method="POST" action="{{ route('clients.update', $client) }}">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2"><h3 class="text-lg font-semibold text-purple-300 mb-4 border-b border-white/10 pb-2">Firm Information</h3></div>
                    <div class="md:col-span-2">
                        <label class="block text-purple-200 text-sm font-medium mb-1">Firm Name *</label>
                        <input type="text" name="name" id="firm_name" required value="{{ old('name', $client->name) }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500">
                        <p class="mt-1 text-xs text-purple-300/70">Auto-generated Slug: <span id="slugPreview" class="font-mono text-purple-300">{{ $client->slug }}</span></p>
                        @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div><label class="block text-purple-200 text-sm font-medium mb-1">Proprietor Name</label><input type="text" name="proprietor_name" value="{{ old('proprietor_name', $client->proprietor_name) }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500"></div>
                    <div><label class="block text-purple-200 text-sm font-medium mb-1">Shop Number</label><input type="text" name="shop_number" value="{{ old('shop_number', $client->shop_number) }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500"></div>
                    <div><label class="block text-purple-200 text-sm font-medium mb-1">Phone</label><input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500"></div>
                    <div><label class="block text-purple-200 text-sm font-medium mb-1">City</label><input type="text" name="city" value="{{ old('city', $client->city) }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500"></div>
                    <div class="md:col-span-2"><label class="block text-purple-200 text-sm font-medium mb-1">Address</label><textarea name="address" rows="3" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500">{{ old('address', $client->address) }}</textarea></div>

                    <div class="md:col-span-2 mt-6"><h3 class="text-lg font-semibold text-purple-300 mb-4 border-b border-white/10 pb-2">Account Settings</h3></div>
                    <div>
                        <label class="block text-purple-200 text-sm font-medium mb-1">Username *</label>
                        <input type="text" name="username" required value="{{ old('username', $client->username) }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500" placeholder="e.g., chand_admin">
                        @error('username') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-purple-200 text-sm font-medium mb-1">Admin Email *</label>
                        <input type="email" name="admin_email" required value="{{ old('admin_email', $client->admin_email) }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500">
                        @error('admin_email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="relative">
                        <label class="block text-purple-200 text-sm font-medium mb-1">Password *</label>
                        <input type="password" name="admin_password" id="client_edit_password" required class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 pr-12 outline-none focus:border-purple-500" placeholder="Enter new password">
                        <button type="button" onclick="togglePassword('client_edit_password')" class="absolute right-3 top-9 text-purple-300 hover:text-white">
                            <svg id="client_edit_password_eye_open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg id="client_edit_password_eye_closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532-7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </button>
                        @error('admin_password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-purple-200 text-sm font-medium mb-1">Status</label>
                        <select name="status" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500">
                            <option value="pending" {{ old('status', $client->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('status', $client->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div><label class="block text-purple-200 text-sm font-medium mb-1">Subscription Ends</label><input type="date" name="subscription_ends_at" value="{{ old('subscription_ends_at', $client->subscription_ends_at?->format('Y-m-d')) }}" class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-3 outline-none focus:border-purple-500"></div>
                </div>
                <div class="flex gap-4 mt-8">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-500 hover:to-fuchsia-500 text-white font-bold py-3.5 rounded-lg transition-all shadow-lg">Update Client</button>
                    <a href="{{ route('clients.index') }}" class="px-8 py-3.5 border border-white/20 rounded-lg text-white hover:bg-white/10 transition-all text-center">Cancel</a>
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
        document.addEventListener('DOMContentLoaded', function() {
            var firmName = document.getElementById('firm_name');
            var slugPreview = document.getElementById('slugPreview');
            if (firmName && slugPreview) {
                firmName.addEventListener('input', function() {
                    var name = this.value.trim();
                    if (name.length > 0) {
                        var slug = name.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').substring(0, 30).replace(/-$/, '');
                        slugPreview.textContent = slug || '-';
                    } else {
                        slugPreview.textContent = '-';
                    }
                });
            }
        });
    </script>
</body>
</html>