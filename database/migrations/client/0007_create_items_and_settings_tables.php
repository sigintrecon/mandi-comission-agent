<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('client_provisioning')->create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name_ur')->unique();
            $table->string('name_en')->nullable();
            $table->decimal('default_commission_pct', 5, 2)->default(0);
            $table->decimal('default_mazdoori_rate', 10, 2)->default(0);
            $table->decimal('default_market_fee_rate', 10, 2)->default(0);
            $table->decimal('default_munshiana_rate', 10, 2)->default(0);
            $table->decimal('default_lagana', 10, 2)->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Per (item, party) charge defaults — overrides item defaults
        Schema::connection('client_provisioning')->create('item_charge_defaults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete(); // party account
            $table->decimal('commission_pct', 5, 2)->nullable();
            $table->decimal('mazdoori_rate', 10, 2)->nullable();
            $table->decimal('market_fee_rate', 10, 2)->nullable();
            $table->decimal('munshiana_rate', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'account_id']);
        });

        // Charge head config (commission, mazdoori, etc.)
        Schema::connection('client_provisioning')->create('charge_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name_ur');
            $table->string('name_en');
            $table->string('calc_type')->comment('percent, per_nag, manual');
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Settings (firm name, logo, etc.)
        Schema::connection('client_provisioning')->create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('client_provisioning')->dropIfExists('charge_heads');
        Schema::connection('client_provisioning')->dropIfExists('item_charge_defaults');
        Schema::connection('client_provisioning')->dropIfExists('items');
        Schema::connection('client_provisioning')->dropIfExists('settings');
    }
};
