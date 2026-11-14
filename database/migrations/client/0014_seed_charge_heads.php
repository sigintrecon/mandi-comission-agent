<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // charge_heads needs accounts to exist first.
        // We use the seeded Cash account (code 1) as the default income/expense head.
        // Each firm can override via settings later.

        $cashId = DB::connection('client_provisioning')
            ->table('accounts')
            ->where('code', '1')
            ->value('id');

        // If no cash account with code=1, pick the first Cash-type account
        if (! $cashId) {
            $cashTypeId = DB::connection('client_provisioning')
                ->table('account_types')
                ->where('name_en', 'Cash')
                ->value('id');

            $cashId = DB::connection('client_provisioning')
                ->table('accounts')
                ->where('account_type_id', $cashTypeId)
                ->value('id');
        }

        // If still none, skip seeding — firm hasn't set up accounts yet
        if (! $cashId) {
            return;
        }

        $existing = DB::connection('client_provisioning')
            ->table('charge_heads')
            ->count();

        if ($existing > 0) {
            return; // already seeded
        }

        $now = now();

        DB::connection('client_provisioning')->table('charge_heads')->insert([
            // 1 — Commission  (percent of gross)
            ['name_ur' => 'کمیشن',      'name_en' => 'Commission',   'calc_type' => 'percent',  'account_id' => $cashId, 'is_system' => true,  'is_hidden' => false, 'sort_order' => 1,  'created_at' => $now, 'updated_at' => $now],
            // 2 — Karaya      (per nag)
            ['name_ur' => 'کرایہ',       'name_en' => 'Karaya',       'calc_type' => 'per_nag',  'account_id' => $cashId, 'is_system' => false, 'is_hidden' => false, 'sort_order' => 2,  'created_at' => $now, 'updated_at' => $now],
            // 3 — Kharch      (per nag)
            ['name_ur' => 'خرچہ',        'name_en' => 'Kharch',       'calc_type' => 'per_nag',  'account_id' => $cashId, 'is_system' => false, 'is_hidden' => false, 'sort_order' => 3,  'created_at' => $now, 'updated_at' => $now],
            // 4 — Mazdoori    (per nag)
            ['name_ur' => 'مزدوری',      'name_en' => 'Mazdoori',     'calc_type' => 'per_nag',  'account_id' => $cashId, 'is_system' => false, 'is_hidden' => false, 'sort_order' => 4,  'created_at' => $now, 'updated_at' => $now],
            // 5 — Market fee  (per nag)
            ['name_ur' => 'مارکیٹ',      'name_en' => 'Market Fee',   'calc_type' => 'per_nag',  'account_id' => $cashId, 'is_system' => false, 'is_hidden' => false, 'sort_order' => 5,  'created_at' => $now, 'updated_at' => $now],
            // 6 — Munshiana   (per nag)
            ['name_ur' => 'منشیانہ',     'name_en' => 'Munshiana',    'calc_type' => 'per_nag',  'account_id' => $cashId, 'is_system' => false, 'is_hidden' => false, 'sort_order' => 6,  'created_at' => $now, 'updated_at' => $now],
            // 7 — Stor karaya (manual)
            ['name_ur' => 'سٹور کرایہ',  'name_en' => 'Store Rent',   'calc_type' => 'manual',   'account_id' => $cashId, 'is_system' => false, 'is_hidden' => false, 'sort_order' => 7,  'created_at' => $now, 'updated_at' => $now],
            // 8 — Naqdi bill  (manual — cash payment deducted from payable)
            ['name_ur' => 'نقدی بل',     'name_en' => 'Cash Payment', 'calc_type' => 'manual',   'account_id' => $cashId, 'is_system' => false, 'is_hidden' => false, 'sort_order' => 8,  'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::connection('client_provisioning')
            ->table('charge_heads')
            ->whereIn('name_en', [
                'Commission', 'Karaya', 'Kharch', 'Mazdoori',
                'Market Fee', 'Munshiana', 'Store Rent', 'Cash Payment',
            ])
            ->delete();
    }
};
