<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('client_provisioning')->create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->unique();
            $table->string('name_ur')->unique();
            $table->string('normal_balance')->comment('jama or banam');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // Seed default account types
        DB::connection('client_provisioning')->table('account_types')->insert([
            ['name_en' => 'Party',    'name_ur' => 'بیوپاری', 'normal_balance' => 'jama',  'is_system' => true,  'created_at' => now(), 'updated_at' => now()],
            ['name_en' => 'Customer', 'name_ur' => 'گاہک',    'normal_balance' => 'banam', 'is_system' => true,  'created_at' => now(), 'updated_at' => now()],
            ['name_en' => 'Cash',     'name_ur' => 'نقدی',    'normal_balance' => 'banam', 'is_system' => true,  'created_at' => now(), 'updated_at' => now()],
            ['name_en' => 'Bank',     'name_ur' => 'بینک',    'normal_balance' => 'banam', 'is_system' => true,  'created_at' => now(), 'updated_at' => now()],
            ['name_en' => 'Expense',  'name_ur' => 'خرچہ',    'normal_balance' => 'banam', 'is_system' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name_en' => 'Income',   'name_ur' => 'آمدنی',   'normal_balance' => 'jama',  'is_system' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::connection('client_provisioning')->dropIfExists('account_types');
    }
};
