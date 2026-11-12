<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('client_provisioning')->table('account_types')->insertOrIgnore([
            [
                'name_en' => 'Ras',
                'name_ur' => 'راس',
                'normal_balance' => 'jama',
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'Store Khata',
                'name_ur' => 'سٹور کھاتہ',
                'normal_balance' => 'banam',
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::connection('client_provisioning')->table('recovery_groups')
            ->whereIn('name_en', ['Angoor', 'Khubani'])
            ->update(['is_active' => false, 'updated_at' => now()]);

        DB::connection('client_provisioning')->table('recovery_groups')
            ->where('name_en', 'New')
            ->update(['name_ur' => 'NEW', 'is_active' => true, 'updated_at' => now()]);

        DB::connection('client_provisioning')->table('recovery_groups')
            ->where('name_en', 'Dead')
            ->update(['name_ur' => 'DEAD', 'is_active' => true, 'updated_at' => now()]);
    }

    public function down(): void
    {
        foreach (['Ras', 'Store Khata'] as $name) {
            $accountTypeId = DB::connection('client_provisioning')->table('account_types')
                ->where('name_en', $name)
                ->value('id');

            if ($accountTypeId !== null && ! DB::connection('client_provisioning')->table('accounts')
                ->where('account_type_id', $accountTypeId)
                ->exists()) {
                DB::connection('client_provisioning')->table('account_types')
                    ->where('id', $accountTypeId)
                    ->delete();
            }
        }

        DB::connection('client_provisioning')->table('recovery_groups')
            ->whereIn('name_en', ['Angoor', 'Khubani'])
            ->update(['is_active' => true, 'updated_at' => now()]);
    }
};
