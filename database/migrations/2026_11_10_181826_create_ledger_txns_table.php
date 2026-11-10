<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_txns', function (Blueprint $table) {
            $table->id();
            $table->uuid('idempotency_key')->unique()->comment('Prevents double-posting on retry');
            $table->string('source_type')->comment('bill, voucher, opening');
            $table->unsignedBigInteger('source_id')->nullable()->comment('bill_id or voucher_id');
            $table->date('entry_date');
            $table->string('narration')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_txns');
    }
};
