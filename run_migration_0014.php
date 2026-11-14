<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\DB;

// Run from project root: php run_migration_0014.php
// Runs ONLY the 0014 charge heads seed migration

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);

$app->boot();

$migFile = __DIR__.'/database/migrations/client/0014_seed_charge_heads.php';

$migration = include $migFile;

// Set provisioning connection same as app default (local dev = mysql or sqlite)
$baseConn = config('database.default');
$connConfig = config("database.connections.{$baseConn}");

// You need to set database_name to YOUR CLIENT DB here
// Get it from mandi_master.clients table
$clientDb = env('CLIENT_MIGRATION_DB', ''); // set in .env as CLIENT_MIGRATION_DB=mandi_client_yourslug

if (empty($clientDb)) {
    echo "[ERROR] Set CLIENT_MIGRATION_DB=mandi_client_yourslug in your .env file first!\n";
    exit(1);
}

config([
    'database.connections.client_provisioning' => array_merge($connConfig, [
        'database' => $clientDb,
    ]),
]);

DB::purge('client_provisioning');

echo "Running 0014_seed_charge_heads on: {$clientDb}\n";
$migration->up();
echo "[OK] Done!\n";
