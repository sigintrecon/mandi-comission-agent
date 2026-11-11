<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Client-side users table (DEO, Viewer roles per client)
        Schema::connection('client_provisioning')->create('client_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'deo', 'viewer'])->default('deo');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('client_provisioning')->create('ledger_txns', function (Blueprint $table) {
            $table->id();
            $table->uuid('idempotency_key')->unique();
            $table->string('source_type')->comment('bill, voucher, opening');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->date('entry_date');
            $table->string('narration')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->comment('client_users.id');
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index('entry_date');
        });

        Schema::connection('client_provisioning')->create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_txn_id')->constrained('ledger_txns')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('narration')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index(['ledger_txn_id', 'account_id']);
        });

        DB::connection('client_provisioning')->statement(
            'ALTER TABLE ledger_entries ADD CONSTRAINT chk_debit_credit_integrity
             CHECK (debit >= 0 AND credit >= 0 AND (debit = 0 OR credit = 0) AND (debit + credit) > 0)'
        );
    }

    public function down(): void
    {
        DB::connection('client_provisioning')->statement(
            'ALTER TABLE ledger_entries DROP CONSTRAINT IF EXISTS chk_debit_credit_integrity'
        );
        Schema::connection('client_provisioning')->dropIfExists('ledger_entries');
        Schema::connection('client_provisioning')->dropIfExists('ledger_txns');
        Schema::connection('client_provisioning')->dropIfExists('client_users');
    }
};
