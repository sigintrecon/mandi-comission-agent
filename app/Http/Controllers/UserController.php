<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        if (! auth()->user()->canDeleteUser($user) && $user->id !== auth()->id()) {
            abort(403, 'Cannot edit this user.');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (! auth()->user()->canDeleteUser($user) && $user->id !== auth()->id()) {
            abort(403, 'Cannot update this user.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
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

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        AuditLog::log('updated', 'User', $user->id, $oldValues, $user->fresh()->toArray());

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if (! auth()->user()->canDeleteUser($user)) {
            return back()->with('error', 'You cannot delete this user.');
        }

        AuditLog::log('deleted', 'User', $user->id, $user->toArray(), null);
        $user->delete();

        return back()->with('success', 'User deleted successfully!');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ]);

        $users = User::whereIn('id', $validated['user_ids'])->get();

        foreach ($users as $user) {
            if (! $request->user()->canDeleteUser($user)) {
                return back()->with('error', "You cannot perform bulk actions on {$user->name}.");
            }
        }

        try {
            DB::transaction(function () use ($users, $validated): void {
                foreach ($users as $user) {
                    switch ($validated['action']) {
                        case 'activate':
                            $oldValues = ['is_active' => $user->is_active];
                            $user->update(['is_active' => true]);
                            AuditLog::log('activated', 'User', $user->id, $oldValues, ['is_active' => true]);
                            break;

                        case 'deactivate':
                            $oldValues = ['is_active' => $user->is_active];
                            $user->update(['is_active' => false]);
                            AuditLog::log('deactivated', 'User', $user->id, $oldValues, ['is_active' => false]);
                            break;

                        case 'delete':
                            AuditLog::log('deleted', 'User', $user->id, $user->toArray(), null);
                            $user->delete();
                            break;
                    }
                }
            });

            $count = $users->count();
            $message = match ($validated['action']) {
                'activate' => "$count user(s) activated successfully!",
                'deactivate' => "$count user(s) deactivated successfully!",
                'delete' => "$count user(s) deleted successfully!",
            };

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Bulk action failed: '.$e->getMessage());
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
