<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$clientDb = env('CLIENT_MIGRATION_DB', '');

if (empty($clientDb)) {
    echo "[ERROR] Set CLIENT_MIGRATION_DB in your .env file first!\n";
    exit(1);
}

$baseConnection = config('database.default');
$connectionConfig = config("database.connections.{$baseConnection}");

config([
    'database.connections.client_provisioning' => array_merge($connectionConfig, [
        'database' => $clientDb,
    ]),
]);

DB::purge('client_provisioning');
DB::connection('client_provisioning')->statement('SELECT 1');

$migration = require __DIR__.'/database/migrations/client/0015_add_global_bill_sequence.php';

echo "Running 0015_add_global_bill_sequence on: {$clientDb}\n";
$migration->up();
echo "[OK] Done!\n";
