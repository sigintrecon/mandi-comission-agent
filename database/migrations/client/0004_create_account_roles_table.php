<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('client_provisioning')->create('account_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_key')->unique()->comment('cash, difference, lagana, sale_purchase, commission');
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('client_provisioning')->dropIfExists('account_roles');
    }
};
