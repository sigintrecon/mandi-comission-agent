<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('client_provisioning')->create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('account_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('recovery_group_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name_ur');
            $table->string('name_en')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();

            $table->decimal('commission_pct', 5, 2)->default(0);
            $table->decimal('default_lagana', 10, 2)->default(0);
            $table->string('trader_rate_mode')->default('none')->comment('none, fixed, less_than_rate');
            $table->decimal('trader_rate_value', 10, 2)->default(0);
            $table->text('bank_details')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['code', 'name_ur', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::connection('client_provisioning')->dropIfExists('accounts');
    }
};
