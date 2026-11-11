<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Details - Mandi Manager Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-animated { background: linear-gradient(-45deg, #0f0c29, #302b63, #24243e, #1a0b2e); background-size: 400% 400%; animation: gradientBG 15s ease infinite; }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="bg-animated min-h-screen text-white">
    <nav class="glass-card border-b border-white/10 mb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/elemeentra-logo.jpeg') }}" alt="Logo" class="h-10 w-auto">
                    <div>
                        <h1 class="text-xl font-bold">Mandi Manager Pro</h1>
                        <p class="text-xs text-purple-200/70">Super Admin Portal</p>
                    </div>
                </div>
                <a href="{{ route('clients.index') }}" class="text-purple-200 hover:text-white">â† Back to Clients</a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
    @if(session('success'))<div class="bg-green-500/20 border border-green-500/40 text-green-300 p-4 rounded-xl mb-6">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="bg-red-500/20 border border-red-500/40 text-red-300 p-4 rounded-xl mb-6">{{ session('error') }}</div>@endif
    @if(session('info'))<div class="bg-blue-500/20 border border-blue-500/40 text-blue-300 p-4 rounded-xl mb-6">{{ session('info') }}</div>@endif

    <div class="glass-card rounded-xl p-4 border border-white/10 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🗄️</span>
            <div>
                <p class="text-sm font-semibold text-white">Database: <span class="font-mono text-purple-300">{{ $client->database_name }}</span></p>
                @if($dbExists)
                    <p class="text-xs text-green-400">✅ Database exists and is ready</p>
                @else
                    <p class="text-xs text-red-400">❌ Database NOT found — provisioning required</p>
                @endif
            </div>
        </div>
        @if(!$dbExists && auth()->user()->canManageClients())
            <form method="POST" action="{{ route('clients.reprovision', $client) }}">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                    🔧 Provision Database
                </button>
            </form>
        @endif
    </div>
        <div class="glass-card rounded-2xl p-8 border border-white/10">
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h2 class="text-3xl font-bold mb-2">{{ $client->name }}</h2>
                    <p class="text-purple-200/70">Client Details & Information</p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold 
                    @if($client->status === 'active') bg-green-500/20 text-green-400
                    @elseif($client->status === 'pending') bg-yellow-500/20 text-yellow-400
                    @else bg-red-500/20 text-red-400 @endif">
                    {{ ucfirst($client->status) }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Firm Information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-purple-300 border-b border-white/10 pb-2">Firm Information</h3>
                    
                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Firm Name</label>
                        <p class="text-white font-medium">{{ $client->name }}</p>
                    </div>

                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Proprietor Name</label>
                        <p class="text-white">{{ $client->proprietor_name ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Shop Number</label>
                        <p class="text-white">{{ $client->shop_number ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-purple-200 text-sm mb-1">City</label>
                        <p class="text-white">{{ $client->city ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Address</label>
                        <p class="text-white">{{ $client->address ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Phone</label>
                        <p class="text-white">{{ $client->phone ?? '-' }}</p>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-purple-300 border-b border-white/10 pb-2">Account Information</h3>
                    
                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Database Name</label>
                        <p class="text-purple-300 font-mono text-sm">{{ $client->database_name }}</p>
                    </div>

                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Admin Email</label>
                        <p class="text-white">{{ $client->admin_email }}</p>
                    </div>

                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Subscription Ends</label>
                        <p class="text-white">{{ $client->subscription_ends_at ? $client->subscription_ends_at->format('M d, Y') : 'Not set' }}</p>
                    </div>

                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Created At</label>
                        <p class="text-white">{{ $client->created_at->format('M d, Y h:i A') }}</p>
                    </div>

                    <div>
                        <label class="block text-purple-200 text-sm mb-1">Last Login</label>
                        <p class="text-white">{{ $client->last_login_at ? $client->last_login_at->format('M d, Y h:i A') : 'Never' }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 mt-10 pt-6 border-t border-white/10">
                <a href="{{ route('clients.edit', $client) }}" class="flex-1 bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-500 hover:to-fuchsia-500 text-white font-bold py-3 rounded-lg transition-all text-center">
                    Edit Client
                </a>
                <button onclick="showPasswordModal()" class="px-6 py-3 bg-yellow-600 hover:bg-yellow-500 text-white font-bold rounded-lg transition-all">
                    Change Password
                </button>
                <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this client? This action cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-bold rounded-lg transition-all">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- Password Change Modal -->
    <div id="passwordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="glass-card rounded-2xl p-8 w-full max-w-md relative border border-purple-500/30">
            <button onclick="closePasswordModal()" class="absolute top-4 right-4 text-white/50 hover:text-white">âœ•</button>
            <h3 class="text-2xl font-bold text-white mb-6">Change Password</h3>
            <form method="POST" action="{{ route('clients.change-password', $client) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-purple-200 text-sm mb-1">New Password</label>
                    <input type="password" name="new_password" required class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-2.5 outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-purple-200 text-sm mb-1">Confirm Password</label>
                    <input type="password" name="new_password_confirmation" required class="w-full bg-gray-800/50 text-white border border-purple-500/30 rounded-lg px-4 py-2.5 outline-none focus:border-purple-500">
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white font-bold py-3 rounded-lg transition-all mt-4">Change Password</button>
            </form>
        </div>
    </div>

    <script>
        function showPasswordModal() {
            document.getElementById('passwordModal').classList.remove('hidden');
        }
        function closePasswordModal() {
            document.getElementById('passwordModal').classList.add('hidden');
        }
    </script>
</body>
</html>
