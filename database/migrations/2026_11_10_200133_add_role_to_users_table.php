<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'client_manager', 'viewer'])->default('viewer')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
        });

        // Set existing user (ID 1) as super_admin
        DB::table('users')->where('id', 1)->update(['role' => 'super_admin', 'is_active' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};