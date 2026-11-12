<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Services\ClientPortalDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClientPortalRoutesTest extends TestCase
{
    use RefreshDatabase;

    private ?string $tenantDatabasePath = null;

    protected function tearDown(): void
    {
        DB::purge('client_portal');
        DB::purge('client_provisioning');

        if ($this->tenantDatabasePath && file_exists($this->tenantDatabasePath)) {
            unlink($this->tenantDatabasePath);
        }

        parent::tearDown();
    }

    public function test_dashboard_renders_with_registered_portal_links(): void
    {
        $client = $this->createActiveClient();

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->get(route('client.portal.dashboard', ['slug' => $client->slug]));

        $response->assertOk()
            ->assertSee(route('client.portal.accounts.create', ['slug' => $client->slug]))
            ->assertSee(route('client.portal.items.create', ['slug' => $client->slug]))
            ->assertSee(route('client.portal.bills.sale', ['slug' => $client->slug]))
            ->assertSee(route('client.portal.reports.dailybook', ['slug' => $client->slug]))
            ->assertSee(route('client.portal.ai-settings', ['slug' => $client->slug]));
    }

    public function test_client_ai_api_key_is_encrypted_in_storage(): void
    {
        $client = $this->createActiveClient(['ai_api_key' => 'test-provider-secret']);

        $storedKey = DB::table('clients')->where('id', $client->id)->value('ai_api_key');

        $this->assertNotSame('test-provider-secret', $storedKey);
        $this->assertSame('test-provider-secret', $client->fresh()->ai_api_key);
        $this->assertArrayNotHasKey('ai_api_key', $client->fresh()->toArray());
    }

    public function test_client_can_use_its_ai_key_to_send_a_chat_message(): void
    {
        $client = $this->createActiveClient(['ai_api_key' => 'test-provider-secret']);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Test reply']],
                ],
            ]),
        ]);

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->postJson(route('client.portal.ai-chat', ['slug' => $client->slug]), [
            'message' => 'Hello',
        ]);

        $response->assertOk()->assertJsonPath('reply', 'Test reply');
        Http::assertSent(fn (ClientRequest $request): bool => $request->hasHeader(
            'Authorization',
            'Bearer test-provider-secret',
        ));
    }

    public function test_portal_page_rejects_a_session_for_another_client(): void
    {
        $client = $this->createActiveClient();

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => 'different-client',
            ],
        ])->get(route('client.portal.accounts.create', ['slug' => $client->slug]));

        $response->assertRedirect(route('client.portal.login', ['slug' => $client->slug]));
    }

    public function test_client_can_create_an_account_in_its_own_database(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantAccountTables($client);
        $this->applyOpeningBalanceMigration($client);

        $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->get(route('client.portal.accounts.create', ['slug' => $client->slug]))
            ->assertOk()
            ->assertSee('ایڈ نیو کھاتہ')
            ->assertSee('id="code"', false)
            ->assertSee('autofocus', false)
            ->assertSee('hidden></div>', false)
            ->assertSee('name="opening_balance"', false)
            ->assertSee('name="opening_balance_side"', false)
            ->assertDontSee('name="opening_credit"', false)
            ->assertDontSee('name="opening_debit"', false);

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->post(route('client.portal.accounts.store', ['slug' => $client->slug]), [
            'code' => 'C-100',
            'account_type_id' => 1,
            'name_ur' => 'نیا کسٹمر',
            'name_en' => 'New customer',
            'opening_balance' => 0,
            'opening_balance_side' => 'jama',
        ]);

        $response->assertRedirect(route('client.portal.accounts.create', ['slug' => $client->slug]));
        $this->assertDatabaseHas('accounts', [
            'code' => 'C-100',
            'name_ur' => 'نیا کسٹمر',
            'name_en' => 'New customer',
        ], 'client_portal');
    }

    public function test_account_form_suggests_the_next_numeric_code(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantAccountTables($client);
        $this->applyOpeningBalanceMigration($client);
        DB::connection('client_portal')->table('accounts')->insert([
            'code' => '4001',
            'account_type_id' => 1,
            'name_ur' => 'Existing account',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->get(route('client.portal.accounts.create', ['slug' => $client->slug]));

        $response->assertOk()->assertSee('value="4002"', false);
    }

    public function test_entering_an_existing_account_code_updates_its_details(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantAccountTables($client);
        $this->applyOpeningBalanceMigration($client);
        $accountId = DB::connection('client_portal')->table('accounts')->insertGetId([
            'code' => '4001',
            'account_type_id' => 1,
            'name_ur' => 'Existing account',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->post(route('client.portal.accounts.store', ['slug' => $client->slug]), [
            'code' => ' 4001 ',
            'created_date' => '2026-11-12',
            'account_type_id' => 1,
            'name_ur' => 'Updated account',
            'name_en' => 'Updated name',
            'city' => 'Faisalabad',
            'phone' => '03001234567',
            'opening_balance' => 250,
            'opening_balance_side' => 'jama',
        ]);

        $response->assertRedirect(route('client.portal.accounts.create', ['slug' => $client->slug]))
            ->assertSessionHas('success', 'کھاتے کی تفصیلات کامیابی سے اپ ڈیٹ ہوگئیں۔');
        $this->assertDatabaseHas('accounts', [
            'id' => $accountId,
            'code' => '4001',
            'name_ur' => 'Updated account',
            'name_en' => 'Updated name',
            'city' => 'Faisalabad',
            'phone' => '03001234567',
            'opening_credit' => 250,
            'opening_debit' => 0,
            'created_at' => '2026-11-12 00:00:00',
        ], 'client_portal');
        $this->assertDatabaseCount('accounts', 1, 'client_portal');
    }

    public function test_account_lookup_returns_the_existing_account_fields(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantAccountTables($client);
        $this->applyOpeningBalanceMigration($client);
        DB::connection('client_portal')->table('accounts')->insert([
            'code' => '4001',
            'account_type_id' => 1,
            'name_ur' => 'Existing account',
            'name_en' => 'Existing name',
            'city' => 'Faisalabad',
            'opening_debit' => 75,
            'created_at' => '2026-11-12 00:00:00',
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->getJson(route('client.portal.accounts.lookup', [
            'slug' => $client->slug,
            'code' => '4001',
        ]));

        $response->assertOk()
            ->assertJsonPath('account.code', '4001')
            ->assertJsonPath('account.name_ur', 'Existing account')
            ->assertJsonPath('account.name_en', 'Existing name')
            ->assertJsonPath('account.city', 'Faisalabad')
            ->assertJsonPath('account.created_date', '2026-11-12')
            ->assertJsonPath('account.opening_balance', 75)
            ->assertJsonPath('account.opening_balance_side', 'banam');
    }

    public function test_account_form_orders_customer_first_and_only_shows_supported_recovery_groups(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantAccountTables($client);
        $this->applyOpeningBalanceMigration($client);
        DB::connection('client_portal')->table('account_types')->insert([
            [
                'name_en' => 'Bank',
                'name_ur' => 'بینک',
                'normal_balance' => 'banam',
                'is_system' => true,
            ],
            [
                'name_en' => 'Ras',
                'name_ur' => 'راس',
                'normal_balance' => 'jama',
                'is_system' => false,
            ],
            [
                'name_en' => 'Store Khata',
                'name_ur' => 'سٹور کھاتہ',
                'normal_balance' => 'banam',
                'is_system' => false,
            ],
        ]);
        DB::connection('client_portal')->table('recovery_groups')->insert([
            ['name_ur' => 'NEW', 'name_en' => 'New'],
            ['name_ur' => 'DEAD', 'name_en' => 'Dead'],
            ['name_ur' => 'انگور', 'name_en' => 'Angoor'],
            ['name_ur' => 'خوبانی', 'name_en' => 'Khubani'],
        ]);
        $this->applyAccountTypeAndRecoveryGroupMigration($client);

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->get(route('client.portal.accounts.create', ['slug' => $client->slug]));

        $response->assertOk()
            ->assertSeeInOrder([
                'گاہک (Customer)',
                'بینک (Bank)',
                'راس (Ras)',
                'اسٹاف (STAFF)',
                'سٹور کھاتہ (Store Khata)',
                'New',
                'Dead/Old',
            ])
            ->assertDontSee('منتخب کریں')
            ->assertDontSee('کوئی نہیں')
            ->assertDontSee('Angoor')
            ->assertDontSee('Khubani');
        $response->assertSee('value="1" selected', false)
            ->assertSee('value="1" selected', false)
            ->assertSee('id="opening_balance_side"', false)
            ->assertSee('value="jama" selected', false);
        $this->assertDatabaseHas('account_types', [
            'name_en' => 'Ras',
            'name_ur' => 'راس',
            'normal_balance' => 'jama',
        ], 'client_portal');
        $this->assertDatabaseHas('account_types', [
            'name_en' => 'Store Khata',
            'name_ur' => 'سٹور کھاتہ',
            'normal_balance' => 'banam',
        ], 'client_portal');
        $this->assertDatabaseHas('account_types', [
            'name_en' => 'STAFF',
            'name_ur' => 'اسٹاف',
            'normal_balance' => 'banam',
        ], 'client_portal');
        $this->assertDatabaseHas('recovery_groups', [
            'name_en' => 'Angoor',
            'is_active' => false,
        ], 'client_portal');
    }

    public function test_account_list_filters_and_shows_opening_balance_side_without_recovery_group_column(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantAccountTables($client);
        $this->applyOpeningBalanceMigration($client);
        DB::connection('client_portal')->table('recovery_groups')->insert([
            'name_ur' => 'NEW',
            'name_en' => 'New',
            'is_active' => true,
        ]);
        $createdAt = now();
        $accountId = DB::connection('client_portal')->table('accounts')->insertGetId([
            'code' => 'STAFF-10',
            'account_type_id' => 1,
            'recovery_group_id' => 1,
            'name_ur' => 'Test account',
            'name_en' => 'Filterable name',
            'city' => 'Faisalabad',
            'phone' => '03001234567',
            'opening_credit' => 500,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->get(route('client.portal.accounts.create', ['slug' => $client->slug]));

        $response->assertOk()
            ->assertSee('id="searchInput"', false)
            ->assertSee('id="filterAccountType"', false)
            ->assertSee('id="filterBalanceSide"', false)
            ->assertDontSee('id="filterRecoveryGroup"', false)
            ->assertDontSee('id="filterCity"', false)
            ->assertSee('data-account-type="1"', false)
            ->assertSee('data-balance-side="jama"', false)
            ->assertSee('جمع (Credit)')
            ->assertSee('بنام (Debit)')
            ->assertDontSee('(Jama)')
            ->assertDontSee('(Banam)')
            ->assertSee('Filterable name')
            ->assertDontSee('گاہک قسم')
            ->assertDontSee('500 (جمع)')
            ->assertSee('.fcard{background:#fff;border:10px groove #1565c0;border-radius:10px;margin:12px;padding:14px;flex:0 0 auto;max-height:calc(100vh - 230px);overflow-y:auto}', false)
            ->assertSee('.tbl-wrap{margin:12px;overflow:auto;flex:1 1 auto;min-height:0;max-height:none}', false)
            ->assertSee('#main{margin-right:var(--sw);margin-left:var(--sw);flex:1;display:flex;flex-direction:column;height:100vh;min-height:0;overflow:hidden}', false);

        $tableDocument = new \DOMDocument;
        $tableDocument->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($tableDocument);
        $headers = array_map(
            static fn (\DOMNode $header): string => trim($header->textContent),
            iterator_to_array($xpath->query('//table[@id="accountTable"]/thead/tr/th'))
        );
        $firstRow = array_map(
            static fn (\DOMNode $cell): string => trim($cell->textContent),
            iterator_to_array($xpath->query('//table[@id="accountTable"]/tbody/tr[1]/td'))
        );

        $this->assertSame(
            ['پارٹی نمبر', 'تاریخ', 'نام انگلش', 'نام اردو', 'کھاتہ قسم', 'اوپننگ بیلنس', 'جمع / بنام', 'فون نمبر#', 'پتہ', 'کمیشن', ''],
            $headers
        );
        $this->assertSame(
            ['STAFF-10', $createdAt->format('Y-m-d'), 'Filterable name', 'Test account', 'گاہک', '500', 'جمع (Credit)', '03001234567', 'Faisalabad', '0', ''],
            $firstRow
        );

        $this->assertDatabaseHas('accounts', ['id' => $accountId], 'client_portal');
    }

    public function test_banam_opening_balance_is_stored_in_the_debit_column(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantAccountTables($client);
        $this->applyOpeningBalanceMigration($client);

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->post(route('client.portal.accounts.store', ['slug' => $client->slug]), [
            'code' => 'STAFF-1',
            'account_type_id' => 1,
            'name_ur' => 'Staff account',
            'opening_balance' => 1250,
            'opening_balance_side' => 'banam',
        ]);

        $response->assertRedirect(route('client.portal.accounts.create', ['slug' => $client->slug]));
        $this->assertDatabaseHas('accounts', [
            'code' => 'STAFF-1',
            'opening_credit' => 0,
            'opening_debit' => 1250,
        ], 'client_portal');
    }

    public function test_client_can_delete_an_account_without_references(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantAccountTables($client);
        $this->applyOpeningBalanceMigration($client);
        $accountId = DB::connection('client_portal')->table('accounts')->insertGetId([
            'code' => '4001',
            'account_type_id' => 1,
            'name_ur' => 'Account to delete',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $session = [
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ];

        $this->withSession($session)
            ->get(route('client.portal.accounts.create', ['slug' => $client->slug]))
            ->assertOk()
            ->assertSee(route('client.portal.accounts.destroy', [
                'slug' => $client->slug,
                'account' => $accountId,
            ]));

        $response = $this->withSession($session)->delete(route('client.portal.accounts.destroy', [
            'slug' => $client->slug,
            'account' => $accountId,
        ]));

        $response->assertRedirect(route('client.portal.accounts.create', ['slug' => $client->slug]));
        $this->assertDatabaseMissing('accounts', ['id' => $accountId], 'client_portal');
    }

    public function test_client_cannot_delete_an_account_with_references(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantAccountTables($client);
        $this->applyOpeningBalanceMigration($client);
        $accountId = DB::connection('client_portal')->table('accounts')->insertGetId([
            'code' => '4001',
            'account_type_id' => 1,
            'name_ur' => 'Account with references',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Schema::connection('client_portal')->create('account_references', function ($table): void {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
        });
        DB::connection('client_portal')->table('account_references')->insert([
            'account_id' => $accountId,
        ]);

        $response = $this->followingRedirects()->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->delete(route('client.portal.accounts.destroy', [
            'slug' => $client->slug,
            'account' => $accountId,
        ]));

        $response->assertOk()
            ->assertSee('یہ کھاتہ پہلے سے لین دین یا سسٹم کی ترتیبات میں استعمال ہو رہا ہے، اس لیے حذف نہیں کیا جا سکتا۔');
        $this->assertDatabaseHas('accounts', ['id' => $accountId], 'client_portal');
        $this->assertDatabaseHas('account_references', ['account_id' => $accountId], 'client_portal');
    }

    private function createActiveClient(array $attributes = []): Client
    {
        return Client::query()->create([
            'name' => 'Test Mandi',
            'slug' => 'test-mandi',
            'database_name' => 'test_mandi_database',
            'admin_email' => 'test-mandi@example.com',
            'admin_password' => 'password',
            'status' => 'active',
            ...$attributes,
        ]);
    }

    private function createTenantAccountTables(Client $client): void
    {
        app(ClientPortalDatabase::class)->connection($client);

        Schema::connection('client_portal')->create('account_types', function ($table): void {
            $table->id();
            $table->string('name_en')->unique();
            $table->string('name_ur')->unique();
            $table->string('normal_balance');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
        Schema::connection('client_portal')->create('recovery_groups', function ($table): void {
            $table->id();
            $table->string('name_ur');
            $table->string('name_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::connection('client_portal')->create('items', function ($table): void {
            $table->id();
            $table->string('name_ur');
            $table->boolean('is_active')->default(true);
        });
        Schema::connection('client_portal')->create('settings', function ($table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });
        Schema::connection('client_portal')->create('accounts', function ($table): void {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('account_type_id');
            $table->unsignedBigInteger('recovery_group_id')->nullable();
            $table->string('name_ur');
            $table->string('name_en')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('commission_pct', 5, 2)->default(0);
            $table->decimal('default_lagana', 10, 2)->default(0);
            $table->text('bank_details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::connection('client_portal')->table('account_types')->insert([
            'id' => 1,
            'name_en' => 'Customer',
            'name_ur' => 'گاہک',
            'normal_balance' => 'banam',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function applyOpeningBalanceMigration(Client $client): void
    {
        app(ClientPortalDatabase::class)->connection($client);
        config(['database.connections.client_provisioning' => config('database.connections.client_portal')]);
        DB::purge('client_provisioning');

        $migration = require database_path('migrations/client/0010_add_opening_balances_to_accounts.php');
        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::connection('client_provisioning')->hasColumn('accounts', 'opening_credit'));
        $this->assertTrue(Schema::connection('client_provisioning')->hasColumn('accounts', 'opening_debit'));
    }

    private function applyAccountTypeAndRecoveryGroupMigration(Client $client): void
    {
        app(ClientPortalDatabase::class)->connection($client);
        config(['database.connections.client_provisioning' => config('database.connections.client_portal')]);
        DB::purge('client_provisioning');

        $migration = require database_path('migrations/client/0011_update_account_types_and_recovery_groups.php');
        $migration->up();
        $migration->up();

        $staffMigration = require database_path('migrations/client/0012_add_staff_account_type.php');
        $staffMigration->up();
        $staffMigration->up();

        $this->assertSame(
            1,
            DB::connection('client_provisioning')->table('account_types')->where('name_en', 'Ras')->count()
        );
        $this->assertSame(
            1,
            DB::connection('client_provisioning')->table('account_types')->where('name_en', 'STAFF')->count()
        );
        $this->assertFalse(
            (bool) DB::connection('client_provisioning')->table('recovery_groups')
                ->where('name_en', 'Angoor')
                ->value('is_active')
        );
    }
}
