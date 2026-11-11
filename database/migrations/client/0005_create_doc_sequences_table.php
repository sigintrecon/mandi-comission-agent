<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('client_provisioning')->create('doc_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type')->unique()->comment('bill:sale_maal, bill:khareed, bill:customer_kharid, voucher:debit, voucher:credit, voucher:agrai, voucher:jv');
            $table->string('prefix')->default('');
            $table->unsignedBigInteger('current_number')->default(1);
            $table->string('reset_policy')->default('never')->comment('never, annual');
            $table->timestamps();
        });

        DB::connection('client_provisioning')->table('doc_sequences')->insert([
            ['doc_type' => 'bill:sale_maal',        'prefix' => '',  'current_number' => 1, 'reset_policy' => 'never', 'created_at' => now(), 'updated_at' => now()],
            ['doc_type' => 'bill:khareed',          'prefix' => '',  'current_number' => 1, 'reset_policy' => 'never', 'created_at' => now(), 'updated_at' => now()],
            ['doc_type' => 'bill:customer_kharid',  'prefix' => '',  'current_number' => 1, 'reset_policy' => 'never', 'created_at' => now(), 'updated_at' => now()],
            ['doc_type' => 'voucher:debit',         'prefix' => '',  'current_number' => 1, 'reset_policy' => 'never', 'created_at' => now(), 'updated_at' => now()],
            ['doc_type' => 'voucher:credit',        'prefix' => '',  'current_number' => 1, 'reset_policy' => 'never', 'created_at' => now(), 'updated_at' => now()],
            ['doc_type' => 'voucher:agrai',         'prefix' => '',  'current_number' => 1, 'reset_policy' => 'never', 'created_at' => now(), 'updated_at' => now()],
            ['doc_type' => 'voucher:jv',            'prefix' => '',  'current_number' => 1, 'reset_policy' => 'never', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::connection('client_provisioning')->dropIfExists('doc_sequences');
    }
};
