<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('client_provisioning')->create('recovery_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name_ur')->unique()->comment('e.g., NEW, DEAD, انگور, خوبانی');
            $table->string('name_en')->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::connection('client_provisioning')->table('recovery_groups')->insert([
            ['name_ur' => 'NEW',     'name_en' => 'New',    'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name_ur' => 'DEAD',    'name_en' => 'Dead',   'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name_ur' => 'انگور',   'name_en' => 'Angoor', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name_ur' => 'خوبانی', 'name_en' => 'Khubani', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::connection('client_provisioning')->dropIfExists('recovery_groups');
    }
};
