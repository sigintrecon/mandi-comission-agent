<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Client;
use App\Services\ClientDatabaseProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    protected ClientDatabaseProvisioner $provisioner;

    public function __construct(ClientDatabaseProvisioner $provisioner)
    {
        $this->provisioner = $provisioner;
    }

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

        // Create the client record (status = pending initially)
        $validated['status'] = 'pending';
        $client = Client::create($validated);
        AuditLog::log('created', 'Client', $client->id, null, $client->toArray());

        // Auto-provision the client database
        try {
            $this->provisioner->provision($client->database_name, $client->slug);

            // Mark as active after successful provisioning
            $client->update(['status' => 'active']);
            AuditLog::log('provisioned', 'Client', $client->id, ['status' => 'pending'], ['status' => 'active', 'database' => $client->database_name]);

            return redirect()->route('clients.index')
                ->with('success', '✅ Client "'.$client->name.'" created and database provisioned successfully! (DB: '.$client->database_name.')');

        } catch (\Throwable $e) {
            Log::error('Client provisioning failed', [
                'client_id' => $client->id,
                'database' => $client->database_name,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('clients.index')
                ->with('warning', '⚠️ Client "'.$client->name.'" created but database provisioning failed: '.$e->getMessage().'. Status set to pending — retry from client details page.');
        }
    }

    public function show(Client $client)
    {
        $dbExists = $this->provisioner->databaseExists($client->database_name);

        return view('clients.show', compact('client', 'dbExists'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:clients,username,'.$client->id,
            'admin_email' => 'required|email|unique:clients,admin_email,'.$client->id,
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

    public function reprovision(Client $client)
    {
        // Manual re-provision button (from show page, if DB missing)
        try {
            if ($this->provisioner->databaseExists($client->database_name)) {
                return back()->with('info', 'Database already exists: '.$client->database_name);
            }
            $this->provisioner->provision($client->database_name, $client->slug);
            $client->update(['status' => 'active']);
            AuditLog::log('reprovisioned', 'Client', $client->id, null, ['database' => $client->database_name]);

            return back()->with('success', '✅ Database re-provisioned successfully!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Re-provisioning failed: '.$e->getMessage());
        }
    }

    public function destroy(Client $client)
    {
        AuditLog::log('deleted', 'Client', $client->id, $client->toArray(), null);

        // Note: We do NOT auto-drop the DB on delete for safety.
        // Admin must manually drop if needed. This prevents accidental data loss.

        $client->delete();

        return back()->with('success', 'Client deleted. Note: Database "'.$client->database_name.'" was NOT dropped — delete it manually from phpMyAdmin if needed.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,suspend,delete'],
            'client_ids' => ['required', 'array', 'min:1'],
            'client_ids.*' => ['required', 'integer', 'distinct', 'exists:clients,id'],
        ]);

        $clients = Client::whereIn('id', $validated['client_ids'])->get();

        foreach ($clients as $client) {
            if ($validated['action'] === 'delete') {
                AuditLog::log('bulk_deleted', 'Client', $client->id, $client->toArray(), null);
                $client->delete();
            } else {
                $newStatus = $validated['action'] === 'activate' ? 'active' : 'suspended';
                $old = ['status' => $client->status];
                $client->update(['status' => $newStatus]);
                AuditLog::log('bulk_status_change', 'Client', $client->id, $old, ['status' => $newStatus]);
            }
        }

        $actionLabel = match ($validated['action']) {
            'activate' => 'Activated',
            'suspend' => 'Suspended',
            'delete' => 'Deleted',
        };

        return back()->with('success', $actionLabel.' '.$clients->count().' client(s).');
    }
}
