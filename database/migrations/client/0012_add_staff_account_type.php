<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('client_provisioning')->table('account_types')->insertOrIgnore([
            'name_en' => 'STAFF',
            'name_ur' => 'اسٹاف',
            'normal_balance' => 'banam',
            'is_system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $accountTypeId = DB::connection('client_provisioning')->table('account_types')
            ->where('name_en', 'STAFF')
            ->value('id');

        if ($accountTypeId !== null && ! DB::connection('client_provisioning')->table('accounts')
            ->where('account_type_id', $accountTypeId)
            ->exists()) {
            DB::connection('client_provisioning')->table('account_types')
                ->where('id', $accountTypeId)
                ->delete();
        }
    }
};
