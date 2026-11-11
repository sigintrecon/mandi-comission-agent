<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('client_provisioning')->create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('inv_no')->unique();
            $table->string('voucher_type')->comment('debit, credit, agrai, jv');
            $table->date('voucher_date');

            // For debit/credit/agrai: single account pair
            $table->foreignId('account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->decimal('amount', 15, 2)->default(0);

            // For agrai: recovery group
            $table->foreignId('recovery_group_id')->nullable()->constrained('recovery_groups')->nullOnDelete();

            $table->string('narration')->nullable();
            $table->string('status')->default('posted')->comment('posted, reversed');
            $table->unsignedBigInteger('reversal_of')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['voucher_type', 'voucher_date', 'status']);
        });

        // JV lines (multiple debit/credit pairs)
        Schema::connection('client_provisioning')->create('voucher_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('narration')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('client_provisioning')->dropIfExists('voucher_lines');
        Schema::connection('client_provisioning')->dropIfExists('vouchers');
    }
};
