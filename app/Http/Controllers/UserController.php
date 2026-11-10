<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'desc')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,client_manager,viewer',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        AuditLog::log('created', 'User', $user->id, null, $user->toArray());

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        if (!auth()->user()->canDeleteUser($user) && $user->id !== auth()->id()) {
            abort(403, 'Cannot edit this user.');
        }
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->user()->canDeleteUser($user) && $user->id !== auth()->id()) {
            abort(403, 'Cannot update this user.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:admin,client_manager,viewer',
            'is_active' => 'boolean',
        ]);

        $oldValues = $user->toArray();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        AuditLog::log('updated', 'User', $user->id, $oldValues, $user->fresh()->toArray());

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if (!auth()->user()->canDeleteUser($user)) {
            return back()->with('error', 'You cannot delete this user.');
        }

        AuditLog::log('deleted', 'User', $user->id, $user->toArray(), null);
        $user->delete();

        return back()->with('success', 'User deleted successfully!');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array|min:1',
        ]);

        $userIds = $request->input('user_ids');
        $action = $request->input('action');
        $count = count($userIds);

        // Prevent actions on Super Admin (ID 1)
        if (in_array(1, $userIds)) {
            return back()->with('error', 'Cannot perform bulk action on Super Admin (ID 1).');
        }

        DB::beginTransaction();
        try {
            $users = User::whereIn('id', $userIds)->get();

            switch ($action) {
                case 'activate':
                    User::whereIn('id', $userIds)->update(['is_active' => true]);
                    foreach ($users as $user) {
                        AuditLog::log('activated', 'User', $user->id, ['is_active' => false], ['is_active' => true]);
                    }
                    $message = "$count user(s) activated successfully!";
                    break;

                case 'deactivate':
                    User::whereIn('id', $userIds)->update(['is_active' => false]);
                    foreach ($users as $user) {
                        AuditLog::log('deactivated', 'User', $user->id, ['is_active' => true], ['is_active' => false]);
                    }
                    $message = "$count user(s) deactivated successfully!";
                    break;

                case 'delete':
                    foreach ($users as $user) {
                        if (auth()->user()->canDeleteUser($user)) {
                            AuditLog::log('deleted', 'User', $user->id, $user->toArray(), null);
                            $user->delete();
                        }
                    }
                    $message = "Selected users deleted successfully!";
                    break;
            }

            DB::commit();
            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    public function changePassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|min:6|confirmed',
        ]);

        $oldValues = ['password' => '***'];
        $user->password = $request->new_password;
        $user->save();

        AuditLog::log('password_changed', 'User', $user->id, $oldValues, ['password' => '***']);

        return back()->with('success', 'Password changed successfully!');
    }
}