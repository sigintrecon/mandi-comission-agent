<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->unique()->comment('e.g., Party, Customer, Cash, Expense');
            $table->string('name_ur')->unique()->comment('e.g., بیوپاری, گاہک, نقدی, خرچہ');
            $table->string('normal_balance')->comment('jama or banam');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
