<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ $client->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-animated { background: linear-gradient(-45deg, #0f0c29, #302b63, #24243e, #1a0b2e); background-size: 400% 400%; animation: gradientBG 15s ease infinite; }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="bg-animated min-h-screen text-white">
    <nav class="glass-card border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold">{{ $client->name }}</h1>
                </div>
                <form method="POST" action="/{{ $client->slug }}/logout">
                    @csrf
                    <button type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 border border-red-500/30 px-4 py-2 rounded-lg text-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="glass-card rounded-2xl p-10 border border-white/10 text-center">
            <h2 class="text-3xl font-bold mb-4">Welcome to Your Dashboard</h2>
            <p class="text-purple-200/70 mb-8">Database: <span class="font-mono text-purple-300">{{ $client->database_name }}</span></p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                    <h3 class="text-purple-300 font-semibold mb-2">Total Bills</h3>
                    <p class="text-3xl font-bold">0</p>
                </div>
                <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                    <h3 class="text-blue-300 font-semibold mb-2">Outstanding</h3>
                    <p class="text-3xl font-bold">Rs. 0</p>
                </div>
                <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                    <h3 class="text-green-300 font-semibold mb-2">Status</h3>
                    <p class="text-xl font-bold capitalize">{{ $client->status }}</p>
                </div>
            </div>

            <div class="mt-10 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-xl">
                <p class="text-yellow-200 text-sm">🚧 Demo Dashboard: Full client features (Bills, Ledgers, Reports) will be integrated here.</p>
            </div>
        </div>
    </main>
</body>
</html>