<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $totalClients = Client::count();
        $activeClients = Client::where('status', 'active')->count();
        $pendingClients = Client::where('status', 'pending')->count();
        $totalUsers = User::count();
        
        return view('dashboard', compact('totalClients', 'activeClients', 'pendingClients', 'totalUsers'));
    }
}