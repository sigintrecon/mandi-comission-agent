<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('client_provisioning');
        $hasOpeningCredit = $schema->hasColumn('accounts', 'opening_credit');
        $hasOpeningDebit = $schema->hasColumn('accounts', 'opening_debit');

        if (! $hasOpeningCredit || ! $hasOpeningDebit) {
            $schema->table('accounts', function (Blueprint $table) use ($hasOpeningCredit, $hasOpeningDebit): void {
                if (! $hasOpeningCredit) {
                    $table->decimal('opening_credit', 15, 2)->default(0)->after('bank_details');
                }

                if (! $hasOpeningDebit) {
                    $table->decimal('opening_debit', 15, 2)->default(0)->after('opening_credit');
                }
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('client_provisioning');
        $columns = array_values(array_filter(
            ['opening_credit', 'opening_debit'],
            fn (string $column): bool => $schema->hasColumn('accounts', $column),
        ));

        if ($columns !== []) {
            $schema->table('accounts', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
