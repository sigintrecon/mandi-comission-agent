<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert shared global counter for all bill types
        // Sale Maal + Khareed + Customer Kharid share one sequence
        DB::connection('client_provisioning')
            ->table('doc_sequences')
            ->insertOrIgnore([
                'doc_type' => 'bill:global',
                'prefix' => '',
                'current_number' => 1,
                'reset_policy' => 'never',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::connection('client_provisioning')
            ->table('doc_sequences')
            ->where('doc_type', 'bill:global')
            ->delete();
    }
};
