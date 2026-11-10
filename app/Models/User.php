<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Role checks
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isClientManager(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'client_manager']);
    }

    public function isViewer(): bool
    {
        return true; // All users can view
    }

    // Permission checks
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canManageClients(): bool
    {
        return $this->isClientManager();
    }

    public function canViewAuditLogs(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canDeleteUser(User $user): bool
    {
        if ($user->id === 1) return false; // Never delete primary admin
        if ($user->isSuperAdmin() && !$this->isSuperAdmin()) return false;
        return $this->isAdmin();
    }

    // Get role display name
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'client_manager' => 'Client Manager',
            'viewer' => 'Viewer',
            default => 'Unknown',
        };
    }

    // Get role badge color
    public function getRoleBadgeColor(): string
    {
        return match($this->role) {
            'super_admin' => 'bg-purple-500/20 text-purple-300',
            'admin' => 'bg-blue-500/20 text-blue-300',
            'client_manager' => 'bg-green-500/20 text-green-300',
            'viewer' => 'bg-gray-500/20 text-gray-300',
            default => 'bg-gray-500/20 text-gray-300',
        };
    }
}