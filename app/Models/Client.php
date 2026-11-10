<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'username', 'slug', 'database_name', 'admin_email', 'admin_password', 
        'phone', 'address', 'shop_number', 'city', 'proprietor_name',
        'status', 'subscription_ends_at', 'last_login_at'
    ];

    protected $hidden = ['admin_password'];

    protected $casts = [
        'subscription_ends_at' => 'date',
        'last_login_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($client) {
            if (empty($client->slug)) {
                $baseSlug = Str::limit(Str::slug($client->name, '-'), 30, '');
                $slug = $baseSlug;
                $counter = 1;
                while (static::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $client->slug = $slug;
            }
            if (empty($client->database_name)) {
                $client->database_name = 'mandi_client_' . $client->slug;
            }
            // DO NOT hash here - let the mutator handle it
        });
    }

    // Mutator - ALWAYS hash the password when set
    public function setAdminPasswordAttribute($value)
    {
        if (!empty($value)) {
            // Check if already hashed to avoid double-hashing
            if (strlen($value) === 60 && str_starts_with($value, '$2y$')) {
                $this->attributes['admin_password'] = $value;
            } else {
                $this->attributes['admin_password'] = Hash::make($value);
            }
        }
    }
}