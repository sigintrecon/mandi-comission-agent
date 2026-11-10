<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('created_at', 'desc')->paginate(20);
        $totalClients = Client::count();
        $activeClients = Client::where('status', 'active')->count();
        $pendingClients = Client::where('status', 'pending')->count();
        return view('clients.index', compact('clients', 'totalClients', 'activeClients', 'pendingClients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:clients,username',
            'admin_email' => 'required|email|unique:clients,admin_email',
            'admin_password' => 'required|min:6',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'shop_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'proprietor_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,active,suspended',
            'subscription_ends_at' => 'nullable|date',
        ]);
        $validated['status'] = $validated['status'] ?? 'pending';
        $client = Client::create($validated);

        AuditLog::log('created', 'Client', $client->id, null, $client->toArray());

        return redirect()->route('clients.index')->with('success', 'Client "' . $client->name . '" created successfully! (Slug: ' . $client->slug . ')');
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:clients,username,' . $client->id,
            'admin_email' => 'required|email|unique:clients,admin_email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'shop_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'proprietor_name' => 'nullable|string|max:255',
            'status' => 'required|in:pending,active,suspended',
            'subscription_ends_at' => 'nullable|date',
        ]);

        $oldValues = $client->toArray();
        $client->update($validated);

        AuditLog::log('updated', 'Client', $client->id, $oldValues, $client->fresh()->toArray());

        return redirect()->route('clients.index')->with('success', 'Client updated successfully!');
    }

    public function destroy(Client $client)
    {
        AuditLog::log('deleted', 'Client', $client->id, $client->toArray(), null);
        $client->delete();
        return back()->with('success', 'Client deleted.')->with('deleted_client_id', $client->id);
    }

    public function restore($id)
    {
        $client = Client::withTrashed()->find($id);
        if ($client) {
            $client->restore();
            AuditLog::log('restored', 'Client', $client->id, null, $client->toArray());
            return back()->with('success', 'Client restored successfully!');
        }
        return back()->with('error', 'Client not found.');
    }

        public function bulkAction(Request $request)
    {
        // If accessed via GET (e.g., typed in browser), redirect back with a message
        if ($request->isMethod('get')) {
            return redirect()->route('clients.index')->with('error', 'Bulk action requires a POST request. Please use the form on the Clients page.');
        }

        $request->validate([
            'action' => 'required|in:activate,suspend,delete',
            'client_ids' => 'required|array|min:1',
        ]);

        $clientIds = $request->input('client_ids');
        $action = $request->input('action');
        
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $clients = \App\Models\Client::whereIn('id', $clientIds)->get();
            
            if ($action === 'activate') {
                \App\Models\Client::whereIn('id', $clientIds)->update(['status' => 'active']);
            } elseif ($action === 'suspend') {
                \App\Models\Client::whereIn('id', $clientIds)->update(['status' => 'suspended']);
            } elseif ($action === 'delete') {
                foreach ($clients as $client) {
                    \App\Models\AuditLog::log('deleted', 'Client', $client->id, $client->toArray(), null);
                }
                \App\Models\Client::whereIn('id', $clientIds)->delete();
            }
            
            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('clients.index')->with('success', 'Bulk action completed successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->route('clients.index')->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    public function changePassword(Request $request, Client $client)
    {
        $request->validate(['new_password' => 'required|min:6|confirmed']);
        $client->admin_password = $request->new_password;
        $client->save();
        AuditLog::log('password_changed', 'Client', $client->id, null, ['email' => $client->admin_email]);
        return back()->with('success', 'Password changed successfully!');
    }
}