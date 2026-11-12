<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClientPortalDatabase
{
    public function connection(Client $client): Connection
    {
        $baseConnection = config('database.default');
        $connectionConfig = config("database.connections.{$baseConnection}");

        if (! is_array($connectionConfig)) {
            throw new RuntimeException('The application database connection is not configured.');
        }

        config([
            'database.connections.client_portal' => array_merge($connectionConfig, [
                'database' => $client->database_name,
            ]),
        ]);

        DB::purge('client_portal');

        return DB::connection('client_portal');
    }
}
