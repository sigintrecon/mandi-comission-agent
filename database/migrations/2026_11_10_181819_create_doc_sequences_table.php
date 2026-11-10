<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type')->unique()->comment('e.g., bill:sale_maal, voucher:credit');
            $table->string('prefix')->default('');
            $table->unsignedBigInteger('current_number')->default(1);
            $table->string('reset_policy')->default('never')->comment('never, annual');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_sequences');
    }
};
