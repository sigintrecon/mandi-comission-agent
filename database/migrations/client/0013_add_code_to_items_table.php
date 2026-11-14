<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('client_provisioning');

        if ($schema->hasColumn('items', 'code')) {
            return;
        }

        $schema->table('items', function (Blueprint $table) {
            $table->unsignedSmallInteger('code')->nullable()->after('id');
        });

        DB::connection('client_provisioning')
            ->table('items')
            ->get(['id'])
            ->each(function ($row) {
                DB::connection('client_provisioning')
                    ->table('items')
                    ->where('id', $row->id)
                    ->update(['code' => $row->id]);
            });

        $schema->table('items', function (Blueprint $table) {
            $table->unique('code');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('client_provisioning');
        if (! $schema->hasColumn('items', 'code')) {
            return;
        }
        $schema->table('items', function (Blueprint $table) {
            $table->dropUnique('items_code_unique');
            $table->dropColumn('code');
        });
    }
};