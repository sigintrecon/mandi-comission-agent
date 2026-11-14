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

    public function test_sale_bill_search_finds_invoices_older_than_the_recent_list(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantSaleBillSearchTables($client);

        DB::connection('client_portal')->table('bills')->insert([
            'bill_type' => 1,
            'inv_no' => 'OLD-SEARCH-123',
            'bill_date' => '2026-01-01',
            'status' => 'posted',
            'lot_nag' => 1,
        ]);
        foreach (range(1, 50) as $number) {
            DB::connection('client_portal')->table('bills')->insert([
                'bill_type' => 1,
                'inv_no' => 'RECENT-'.$number,
                'bill_date' => '2026-01-02',
                'status' => 'posted',
                'lot_nag' => 1,
            ]);
        }

        $response = $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->get(route('client.portal.bills.sale', [
            'slug' => $client->slug,
            'search' => 'OLD-SEARCH-123',
        ]));

        $response->assertOk()
            ->assertSee('value="OLD-SEARCH-123"', false)
            ->assertSee('OLD-SEARCH-123')
            ->assertDontSee('RECENT-50');

        $this->withSession([
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ])->get(route('client.portal.bills.sale', [
            'slug' => $client->slug,
            'search' => 'RECENT-',
        ]))
            ->assertOk()
            ->assertSee('RECENT-1')
            ->assertSee('RECENT-50')
            ->assertDontSee('OLD-SEARCH-123');
    }

    public function test_new_sale_bill_redirects_to_the_next_invoice_number(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantSaleBillSearchTables($client);
        DB::connection('client_portal')->table('account_types')->insert([
            ['id' => 1, 'name_en' => 'Customer'],
            ['id' => 2, 'name_en' => 'Party'],
        ]);
        DB::connection('client_portal')->table('accounts')->insert([
            ['id' => 1, 'code' => 'C-1', 'account_type_id' => 1, 'is_active' => true, 'name_ur' => 'کسٹمر'],
            ['id' => 2, 'code' => 'P-1', 'account_type_id' => 2, 'is_active' => true, 'name_ur' => 'بیوپاری'],
        ]);
        DB::connection('client_portal')->table('items')->insert([
            ['id' => 1, 'code' => 'I-1', 'name_ur' => 'جنس'],
        ]);
        DB::connection('client_portal')->table('doc_sequences')->insert([
            'doc_type' => 'bill:sale_maal',
            'prefix' => '',
            'current_number' => 1,
        ]);
        $session = [
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ];

        $response = $this->withSession($session)
            ->post(route('client.portal.bills.sale.store', ['slug' => $client->slug]), [
                'inv_no' => '1',
                'bill_date' => '2026-01-03',
                'party_account_id' => 2,
                'item_id' => 1,
                'status' => 'posted',
                'lines' => [[
                    'line_date' => '2026-01-03',
                    'customer_account_id' => 1,
                    'item_id' => 1,
                    'nag' => 2,
                    'wazan' => '',
                    'rate' => 100,
                    'lagana' => 4,
                    'trader_rate' => '',
                ]],
            ]);

        $response->assertRedirect(route('client.portal.bills.sale', ['slug' => $client->slug]));
        $this->assertDatabaseHas('bills', ['inv_no' => '1'], 'client_portal');
        $this->assertDatabaseHas('bill_lines', [
            'nag' => 2,
            'lagana_amount' => 8,
            'cust_amount' => 208,
        ], 'client_portal');
        $this->assertSame(2, DB::connection('client_portal')->table('doc_sequences')
            ->where('doc_type', 'bill:sale_maal')->value('current_number'));

        $this->withSession($session)
            ->get(route('client.portal.bills.sale', ['slug' => $client->slug]))
            ->assertOk()
            ->assertSee('name="inv_no" id="inv_no" value="2"', false);
    }

    public function test_bill_reports_filter_and_paginate_tenant_sale_data(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantSaleBillSearchTables($client);
        DB::connection('client_portal')->table('account_types')->insert([
            ['id' => 1, 'name_en' => 'Customer'],
            ['id' => 2, 'name_en' => 'Party'],
        ]);
        DB::connection('client_portal')->table('accounts')->insert([
            ['id' => 1, 'code' => 'C-1', 'account_type_id' => 1, 'is_active' => true, 'name_ur' => 'کسٹمر'],
            ['id' => 2, 'code' => 'P-1', 'account_type_id' => 2, 'is_active' => true, 'name_ur' => 'بیوپاری'],
        ]);
        DB::connection('client_portal')->table('items')->insert([
            ['id' => 1, 'code' => 'I-1', 'name_ur' => 'سیب'],
        ]);
        foreach (range(1, 11) as $number) {
            $billId = DB::connection('client_portal')->table('bills')->insertGetId([
                'inv_no' => 'SALE-'.$number,
                'bill_type' => 1,
                'bill_date' => '2026-01-01',
                'party_account_id' => 2,
                'item_id' => 1,
                'status' => 'posted',
                'lot_nag' => 2,
            ]);
            DB::connection('client_portal')->table('bill_lines')->insert([
                'bill_id' => $billId,
                'customer_account_id' => 1,
                'item_id' => 1,
                'line_date' => '2026-01-01',
                'nag' => 2,
                'rate' => 100,
                'lagana' => 3,
                'lagana_amount' => 6,
                'cust_amount' => 206,
                'party_amount' => 200,
                'diff_amount' => 0,
                'sort_order' => 0,
            ]);
        }
        DB::connection('client_portal')->table('bills')->insert([
            'inv_no' => 'PUR-1',
            'bill_type' => 2,
            'bill_date' => '2026-01-01',
            'party_account_id' => 2,
            'item_id' => 1,
            'status' => 'posted',
            'lot_nag' => 1,
        ]);
        $session = [
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ];

        $this->withSession($session)
            ->get(route('client.portal.bills.sale.all', ['slug' => $client->slug]))
            ->assertOk()
            ->assertSee('<h1>تمام سیل بل</h1>', false)
            ->assertViewHas('bills', fn ($bills) => $bills->total() === 11 && $bills->count() === 10);

        $this->withSession($session)
            ->get(route('client.portal.bills.all', ['slug' => $client->slug]))
            ->assertOk()
            ->assertSee('PUR-1')
            ->assertViewHas('bills', fn ($bills) => $bills->total() === 12 && $bills->count() === 10);

        $this->withSession($session)
            ->get(route('client.portal.bills.sale.customers', ['slug' => $client->slug]))
            ->assertOk()
            ->assertSee('<h1>تمام کسٹمر وضاحت</h1>', false)
            ->assertViewHas('lines', fn ($lines) => $lines->total() === 11 && $lines->count() === 10);

        $filters = [
            'from' => '2026-01-01',
            'to' => '2026-01-01',
            'customer_id' => 1,
            'item_id' => 1,
            'invoice' => 'SALE-11',
            'per_page' => 25,
        ];
        $this->withSession($session)
            ->get(route('client.portal.bills.sale.all', ['slug' => $client->slug, ...$filters]))
            ->assertOk()
            ->assertSee('SALE-11')
            ->assertDontSee('SALE-10')
            ->assertViewHas('bills', fn ($bills) => $bills->total() === 1 && $bills->count() === 1);

        $this->withSession($session)
            ->get(route('client.portal.bills.sale.customers', ['slug' => $client->slug, ...$filters]))
            ->assertOk()
            ->assertSee('SALE-11')
            ->assertSee('کسٹمر')
            ->assertViewHas('lines', fn ($lines) => $lines->total() === 1 && $lines->count() === 1);
    }

    public function test_sale_invoice_can_be_loaded_and_updated_from_the_bill_list(): void
    {
        $this->tenantDatabasePath = tempnam(sys_get_temp_dir(), 'mandi-tenant-');
        $client = $this->createActiveClient(['database_name' => $this->tenantDatabasePath]);
        $this->createTenantSaleBillSearchTables($client);

        DB::connection('client_portal')->table('account_types')->insert([
            ['id' => 1, 'name_en' => 'Customer'],
            ['id' => 2, 'name_en' => 'Party'],
        ]);
        DB::connection('client_portal')->table('accounts')->insert([
            ['id' => 1, 'code' => 'C-1', 'account_type_id' => 1, 'name_ur' => 'پرانا گاہک'],
            ['id' => 2, 'code' => 'P-1', 'account_type_id' => 2, 'name_ur' => 'پرانا بیوپاری'],
        ]);
        DB::connection('client_portal')->table('items')->insert([
            ['id' => 1, 'code' => 'I-1', 'name_ur' => 'پرانا جنس'],
        ]);
        DB::connection('client_portal')->table('doc_sequences')->insert([
            'doc_type' => 'bill:sale_maal',
            'prefix' => '',
            'current_number' => 4,
        ]);
        $commissionHeadId = DB::connection('client_portal')->table('charge_heads')->insertGetId([
            'name_en' => 'Commission',
            'calc_type' => 'percent',
        ]);
        $karayaHeadId = DB::connection('client_portal')->table('charge_heads')->insertGetId([
            'name_en' => 'Karaya',
            'calc_type' => 'manual',
        ]);
        $billId = DB::connection('client_portal')->table('bills')->insertGetId([
            'inv_no' => 'EDIT-100',
            'bill_type' => 1,
            'bill_date' => '2026-01-01',
            'party_account_id' => 2,
            'item_id' => 1,
            'lot_nag' => 2,
            'status' => 'posted',
        ]);
        DB::connection('client_portal')->table('bill_lines')->insert([
            'bill_id' => $billId,
            'customer_account_id' => 1,
            'item_id' => 1,
            'line_date' => '2026-01-01',
            'nag' => 2,
            'wazan' => null,
            'rate' => 100,
            'lagana' => 0,
            'trader_rate' => null,
            'cust_amount' => 200,
            'party_amount' => 200,
            'lagana_amount' => 0,
            'diff_amount' => 0,
            'sort_order' => 0,
        ]);
        DB::connection('client_portal')->table('bill_charges')->insert([
            'bill_id' => $billId,
            'charge_head_id' => $commissionHeadId,
            'rate' => 1,
            'amount' => 2,
        ]);

        $session = [
            'client_portal_auth' => [
                'client_id' => $client->id,
                'slug' => $client->slug,
            ],
        ];
        $this->withSession($session)
            ->get(route('client.portal.bills.sale', [
                'slug' => $client->slug,
                'search' => 'EDIT-100',
            ]))
            ->assertOk()
            ->assertSee('name="bill_id" id="bill_id" value="'.$billId.'"', false)
            ->assertSee('id="t_lagana" value="0" readonly', false)
            ->assertSee('پرانا جنس')
            ->assertSee('EDIT-100');

        $response = $this->withSession($session)
            ->post(route('client.portal.bills.sale.store', ['slug' => $client->slug]), [
                'bill_id' => $billId,
                'inv_no' => 'EDIT-100',
                'bill_date' => '2026-01-03',
                'party_account_id' => 2,
                'item_id' => 1,
                'marka' => 'Updated',
                'vehicle_no' => 'V-2',
                'container_no' => 'CN-2',
                'status' => 'pending',
                'lines' => [[
                    'line_date' => '2026-01-03',
                    'customer_account_id' => 1,
                    'item_id' => 1,
                    'nag' => 3,
                    'wazan' => '',
                    'rate' => 150,
                    'lagana' => 5,
                    'trader_rate' => '',
                ]],
                'charges' => [
                    'commission_pct' => 2,
                    'karaya' => 10,
                    'kharch' => 0,
                    'mazdoori' => 0,
                    'market' => 0,
                    'munshiana' => 0,
                    'stor_karaya' => 0,
                    'naqdi' => 0,
                ],
            ]);

        $response->assertRedirect(route('client.portal.bills.sale', [
            'slug' => $client->slug,
            'search' => 'EDIT-100',
        ]));
        $this->assertDatabaseHas('bills', [
            'id' => $billId,
            'inv_no' => 'EDIT-100',
            'bill_date' => '2026-01-03',
            'lot_nag' => 3,
            'status' => 'pending',
            'marka' => 'Updated',
        ], 'client_portal');
        $this->assertDatabaseHas('bill_lines', [
            'bill_id' => $billId,
            'nag' => 3,
            'rate' => 150,
            'lagana' => 5,
            'lagana_amount' => 15,
            'cust_amount' => 465,
        ], 'client_portal');
        $this->assertDatabaseHas('bill_charges', [
            'bill_id' => $billId,
            'charge_head_id' => $commissionHeadId,
            'rate' => 2,
        ], 'client_portal');
        $this->assertDatabaseHas('bill_charges', [
            'bill_id' => $billId,
            'charge_head_id' => $karayaHeadId,
            'rate' => 10,
            'amount' => 10,
        ], 'client_portal');
        $this->assertSame(4, DB::connection('client_portal')->table('doc_sequences')
            ->where('doc_type', 'bill:sale_maal')->value('current_number'));

        $response = $this->withSession($session)
            ->post(route('client.portal.bills.sale.store', ['slug' => $client->slug]), [
                'bill_id' => $billId,
                'inv_no' => 'EDIT-100',
                'bill_date' => '2026-01-03',
                'party_account_id' => 2,
                'item_id' => 1,
                'status' => 'posted',
                'lines' => [[
                    'line_date' => '2026-01-03',
                    'customer_account_id' => 1,
                    'item_id' => 1,
                    'nag' => 3,
                    'wazan' => '',
                    'rate' => 150,
                    'lagana' => 5,
                    'trader_rate' => '',
                ]],
                'charges' => ['commission_pct' => 2],
            ]);

        $response->assertRedirect(route('client.portal.bills.sale', [
            'slug' => $client->slug,
            'search' => 'EDIT-100',
        ]));
        $this->assertDatabaseHas('bills', [
            'id' => $billId,
            'status' => 'posted',
        ], 'client_portal');
        $this->assertSame(4, DB::connection('client_portal')->table('doc_sequences')
            ->where('doc_type', 'bill:sale_maal')->value('current_number'));
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

    private function createTenantSaleBillSearchTables(Client $client): void
    {
        app(ClientPortalDatabase::class)->connection($client);

        Schema::connection('client_portal')->create('doc_sequences', function ($table): void {
            $table->id();
            $table->string('doc_type');
            $table->string('prefix')->nullable();
            $table->unsignedBigInteger('current_number');
        });
        Schema::connection('client_portal')->create('account_types', function ($table): void {
            $table->id();
            $table->string('name_en')->unique();
        });
        Schema::connection('client_portal')->create('accounts', function ($table): void {
            $table->id();
            $table->string('code');
            $table->unsignedBigInteger('account_type_id');
            $table->boolean('is_active')->default(true);
            $table->string('name_ur');
            $table->string('name_en')->nullable();
            $table->string('city')->nullable();
            $table->decimal('commission_pct', 5, 2)->default(0);
        });
        Schema::connection('client_portal')->create('items', function ($table): void {
            $table->id();
            $table->string('code');
            $table->string('name_ur');
            $table->string('name_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('default_commission_pct', 5, 2)->default(0);
            $table->decimal('default_mazdoori_rate', 10, 2)->default(0);
            $table->decimal('default_market_fee_rate', 10, 2)->default(0);
            $table->decimal('default_munshiana_rate', 10, 2)->default(0);
            $table->decimal('default_lagana', 10, 2)->default(0);
        });
        Schema::connection('client_portal')->create('charge_heads', function ($table): void {
            $table->id();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('name_en');
            $table->string('calc_type');
        });
        Schema::connection('client_portal')->create('bills', function ($table): void {
            $table->id();
            $table->string('inv_no')->unique();
            $table->unsignedInteger('bill_type');
            $table->date('bill_date');
            $table->unsignedBigInteger('party_account_id')->nullable();
            $table->unsignedBigInteger('marfat_account_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('marka')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('container_no')->nullable();
            $table->string('status');
            $table->decimal('lot_nag', 10, 2)->default(0);
            $table->timestamps();
        });
        Schema::connection('client_portal')->create('bill_lines', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('customer_account_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->date('line_date')->nullable();
            $table->decimal('nag', 10, 2)->default(0);
            $table->decimal('wazan', 10, 2)->nullable();
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('lagana', 10, 2)->default(0);
            $table->decimal('trader_rate', 10, 2)->nullable();
            $table->decimal('cust_amount', 15, 2)->default(0);
            $table->decimal('party_amount', 15, 2)->default(0);
            $table->decimal('lagana_amount', 15, 2)->default(0);
            $table->decimal('diff_amount', 15, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::connection('client_portal')->create('bill_charges', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('charge_head_id');
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });
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
