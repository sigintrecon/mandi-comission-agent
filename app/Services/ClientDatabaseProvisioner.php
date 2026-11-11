<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientDatabaseProvisioner
{
    /**
     * Provision a new client database.
     * Creates DB, creates dedicated MySQL user, runs all client migrations.
     */
    public function provision(string $dbName, string $slug): array
    {
        $results = [];

        // 1. Create the database
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $results['database_created'] = true;
            Log::info("Client DB created: {$dbName}");
        } catch (\Throwable $e) {
            Log::error("Failed to create client DB: {$dbName}", ['error' => $e->getMessage()]);
            throw new \RuntimeException('Database creation failed: '.$e->getMessage());
        }

        // 2. Run client migrations on the new database
        try {
            $this->runClientMigrations($dbName);
            $results['migrations_run'] = true;
            Log::info("Migrations completed for: {$dbName}");
        } catch (\Throwable $e) {
            Log::error("Migration failed for: {$dbName}", ['error' => $e->getMessage()]);
            // Attempt cleanup
            try {
                DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
            } catch (\Throwable $cleanupEx) {
                Log::error("Cleanup also failed for: {$dbName}");
            }
            throw new \RuntimeException('Migration failed: '.$e->getMessage());
        }

        return $results;
    }

    /**
     * Drop a client database (used when client is hard-deleted).
     */
    public function deprovision(string $dbName): void
    {
        try {
            DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
            Log::info("Client DB dropped: {$dbName}");
        } catch (\Throwable $e) {
            Log::error("Failed to drop client DB: {$dbName}", ['error' => $e->getMessage()]);
            throw new \RuntimeException('Database drop failed: '.$e->getMessage());
        }
    }

    /**
     * Run all client-side migrations on the specified database.
     * Uses a temporary connection override.
     */
    protected function runClientMigrations(string $dbName): void
    {
        // Build a temporary connection config pointing to the new DB
        $masterConfig = config('database.connections.mysql');

        config(['database.connections.client_provisioning' => array_merge($masterConfig, [
            'database' => $dbName,
        ])]);

        DB::purge('client_provisioning');

        // Test the connection first
        DB::connection('client_provisioning')->statement('SELECT 1');

        // Run each migration file in order
        $migrationPath = database_path('migrations/client');

        if (! is_dir($migrationPath)) {
            throw new \RuntimeException("Client migrations directory not found: {$migrationPath}");
        }

        $files = glob($migrationPath.'/*.php');
        sort($files); // Ensure alphabetical/timestamp order

        if (empty($files)) {
            throw new \RuntimeException("No client migration files found in: {$migrationPath}");
        }

        foreach ($files as $file) {
            $migration = require $file;
            if (method_exists($migration, 'up')) {
                // Temporarily swap default connection
                $originalConnection = DB::getDefaultConnection();
                DB::setDefaultConnection('client_provisioning');

                try {
                    $migration->up();
                    Log::debug('Ran client migration: '.basename($file));
                } finally {
                    DB::setDefaultConnection($originalConnection);
                }
            }
        }

        // Clean up temp connection
        DB::purge('client_provisioning');
        config(['database.connections.client_provisioning' => null]);
    }

    /**
     * Check if a client database already exists.
     */
    public function databaseExists(string $dbName): bool
    {
        $result = DB::select(
            'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$dbName]
        );

        return ! empty($result);
    }
}
