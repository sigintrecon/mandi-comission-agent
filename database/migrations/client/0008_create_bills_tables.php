<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('client_provisioning')->create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('inv_no')->unique()->comment('From doc_sequences');
            $table->tinyInteger('bill_type')->comment('1=sale_maal, 2=khareed, 3=customer_kharid');
            $table->date('bill_date');

            // Header
            $table->foreignId('party_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->foreignId('marfat_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->restrictOnDelete();
            $table->string('marka')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('container_no')->nullable();

            $table->decimal('lot_nag', 10, 2)->default(0)->comment('Total declared nag');
            $table->string('rounding_mode')->default('half_up')->comment('half_up, none');

            // Status
            $table->string('status')->default('pending')->comment('pending, posted, reversed');
            $table->unsignedBigInteger('reversal_of')->nullable()->comment('bill_id this reverses');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['bill_type', 'bill_date', 'status']);
        });

        Schema::connection('client_provisioning')->create('bill_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('customer_account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->date('line_date')->nullable()->comment('Can differ from bill_date');

            $table->decimal('nag', 10, 2)->default(0);
            $table->decimal('wazan', 10, 2)->nullable()->comment('Weight; if set, used instead of nag for amount');
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('lagana', 10, 2)->default(0);
            $table->decimal('trader_rate', 10, 2)->nullable();

            // Computed (server authority)
            $table->decimal('cust_amount', 15, 2)->default(0);
            $table->decimal('party_amount', 15, 2)->default(0);
            $table->decimal('lagana_amount', 15, 2)->default(0);
            $table->decimal('diff_amount', 15, 2)->default(0);

            $table->decimal('cust_total_override', 15, 2)->nullable()->comment('DEO-typed total override');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('bill_id');
        });

        Schema::connection('client_provisioning')->create('bill_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('charge_head_id')->constrained('charge_heads')->restrictOnDelete();
            $table->decimal('rate', 10, 2)->default(0)->comment('Pct or per-nag rate');
            $table->decimal('amount', 15, 2)->default(0)->comment('Computed amount, server authority');
            $table->timestamps();

            $table->unique(['bill_id', 'charge_head_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('client_provisioning')->dropIfExists('bill_charges');
        Schema::connection('client_provisioning')->dropIfExists('bill_lines');
        Schema::connection('client_provisioning')->dropIfExists('bills');
    }
};
