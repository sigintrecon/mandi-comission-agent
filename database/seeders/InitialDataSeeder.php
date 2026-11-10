<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Account Types (PRD Section 6.2)
        $accountTypes = [
            ['name_en' => 'Party', 'name_ur' => 'بیوپاری', 'normal_balance' => 'jama', 'is_system' => true],
            ['name_en' => 'Customer', 'name_ur' => 'گاہک', 'normal_balance' => 'banam', 'is_system' => true],
            ['name_en' => 'Cash', 'name_ur' => 'نقدی', 'normal_balance' => 'banam', 'is_system' => true],
            ['name_en' => 'Expense', 'name_ur' => 'خرچہ', 'normal_balance' => 'banam', 'is_system' => true],
            ['name_en' => 'Income', 'name_ur' => 'آمدنی', 'normal_balance' => 'jama', 'is_system' => true],
        ];
        DB::table('account_types')->insert($accountTypes);

        // 2. Recovery Groups (PRD Section 9)
        $recoveryGroups = [
            ['name_ur' => 'NEW', 'name_en' => 'NEW', 'is_active' => true],
            ['name_ur' => 'DEAD', 'name_en' => 'DEAD', 'is_active' => true],
            ['name_ur' => 'انگور', 'name_en' => 'Grapes', 'is_active' => true],
            ['name_ur' => 'خوبانی', 'name_en' => 'Apricot', 'is_active' => true],
        ];
        DB::table('recovery_groups')->insert($recoveryGroups);

        // 3. Core System Accounts (PRD Section 7.2)
        // Pehle Account Types ke IDs fetch karte hain
        $partyTypeId = DB::table('account_types')->where('name_en', 'Party')->value('id');
        $customerTypeId = DB::table('account_types')->where('name_en', 'Customer')->value('id');
        $cashTypeId = DB::table('account_types')->where('name_en', 'Cash')->value('id');

        $accounts = [
            ['code' => '4002', 'account_type_id' => $cashTypeId, 'name_ur' => 'نقدی کھاتہ', 'name_en' => 'Cash Account', 'is_active' => true],
            ['code' => '4008', 'account_type_id' => DB::table('account_types')->where('name_en', 'Expense')->value('id'), 'name_ur' => 'ڈیفرنس کھاتہ', 'name_en' => 'Difference A/C', 'is_active' => true],
            ['code' => '4009', 'account_type_id' => DB::table('account_types')->where('name_en', 'Income')->value('id'), 'name_ur' => 'لگانا کھاتہ', 'name_en' => 'Lagana Account', 'is_active' => true],
            ['code' => '4011', 'account_type_id' => $partyTypeId, 'name_ur' => 'خرید و فروخت', 'name_en' => 'Sale Purchase', 'is_active' => true],
        ];
        DB::table('accounts')->insert($accounts);

        // 4. Account Roles Mapping (PRD Section 6.9)
        $cashAccountId = DB::table('accounts')->where('code', '4002')->value('id');
        $diffAccountId = DB::table('accounts')->where('code', '4008')->value('id');
        $laganaAccountId = DB::table('accounts')->where('code', '4009')->value('id');
        $salePurchaseAccountId = DB::table('accounts')->where('code', '4011')->value('id');

        $accountRoles = [
            ['role_key' => 'cash', 'account_id' => $cashAccountId],
            ['role_key' => 'difference', 'account_id' => $diffAccountId],
            ['role_key' => 'lagana', 'account_id' => $laganaAccountId],
            ['role_key' => 'sale_purchase', 'account_id' => $salePurchaseAccountId],
        ];
        DB::table('account_roles')->insert($accountRoles);
    }
}