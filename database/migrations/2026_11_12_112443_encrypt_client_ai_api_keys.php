<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->text('ai_api_key')->nullable()->change();
        });

        DB::table('clients')
            ->whereNotNull('ai_api_key')
            ->orderBy('id')
            ->get(['id', 'ai_api_key'])
            ->each(function (object $client): void {
                try {
                    Crypt::decryptString($client->ai_api_key);
                } catch (DecryptException) {
                    DB::table('clients')
                        ->where('id', $client->id)
                        ->update(['ai_api_key' => Crypt::encryptString($client->ai_api_key)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $apiKeys = DB::table('clients')
            ->whereNotNull('ai_api_key')
            ->orderBy('id')
            ->get(['id', 'ai_api_key'])
            ->map(function (object $client): array {
                try {
                    $plainTextKey = Crypt::decryptString($client->ai_api_key);
                } catch (DecryptException $exception) {
                    throw new RuntimeException('An AI API key could not be decrypted; the column was not changed.', previous: $exception);
                }

                if (strlen($plainTextKey) > 255) {
                    throw new RuntimeException('An AI API key exceeds 255 characters; the column cannot safely be reverted.');
                }

                return ['id' => $client->id, 'key' => $plainTextKey];
            });

        foreach ($apiKeys as $apiKey) {
            DB::table('clients')
                ->where('id', $apiKey['id'])
                ->update(['ai_api_key' => $apiKey['key']]);
        }

        Schema::table('clients', function (Blueprint $table): void {
            $table->string('ai_api_key')->nullable()->change();
        });
    }
};
