<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Client/Firm Name');
            $table->string('slug')->unique()->comment('URL friendly, auto-generated');
            $table->string('database_name')->unique()->comment('e.g., mandi_client_chand');
            $table->string('admin_email')->unique();
            $table->string('admin_password');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('shop_number')->nullable();
            $table->string('city')->nullable();
            $table->string('proprietor_name')->nullable();
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            $table->date('subscription_ends_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};