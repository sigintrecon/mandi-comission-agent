<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\ClientPortalDatabase;
use Illuminate\Database\Connection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClientPortalWorkspaceController extends Controller
{
    public function __construct(private ClientPortalDatabase $databases) {}

    public function page(Request $request): View
    {
        $client = $this->client($request);
        $page = (string) $request->route('page');
        $database = $this->databases->connection($client);
        $rows = collect();
        $accountTypes = $database->table('account_types')->orderBy('name_en')->get();
        $accounts = $database->table('accounts')->where('is_active', true)->orderBy('name_ur')->get();
        $items = $database->table('items')->where('is_active', true)->orderBy('name_ur')->get();
        $recoveryGroups = $database->table('recovery_groups')->where('is_active', true)->orderBy('name_ur')->get();
        $settings = $database->table('settings')->pluck('value', 'key');

        $titles = [
            'accounts.index' => 'تمام کھاتے',
            'accounts.create' => 'نیا کھاتہ',
            'items.create' => 'نئی جنس',
            'bills.index' => 'تمام بل',
            'bills.sale' => 'سیل مال بل',
            'bills.khareed' => 'خرید بل',
            'bills.customer' => 'کسٹمر خرید',
            'vouchers.debit' => 'بنام رقم',
            'vouchers.credit' => 'جمع رقم',
            'vouchers.agrai' => 'وصولی رقم',
            'vouchers.jv' => 'جرنل واؤچر',
            'ledger.party' => 'کھاتہ پارٹی',
            'ledger.banam' => 'بنام کھاتہ',
            'stock.ledger' => 'اسٹاک لیجر',
            'reports.dailybook' => 'روزنامچہ',
            'reports.cash' => 'کیش بک',
            'reports.balance' => 'بیلنس شیٹ',
            'reports.agrai' => 'اگراہی شیٹ',
            'settings' => 'سیٹنگز',
            'ai-settings' => 'AI سیٹنگز',
        ];

        if ($page === 'accounts.index') {
            $rows = $database->table('accounts as a')
                ->join('account_types as t', 't.id', '=', 'a.account_type_id')
                ->orderBy('a.code')
                ->get([
                    'a.code',
                    'a.name_ur',
                    'a.name_en',
                    't.name_ur as type_name',
                    'a.phone',
                    'a.is_active',
                ]);
        } elseif (in_array($page, ['bills.index', 'bills.sale', 'bills.khareed', 'bills.customer'], true)) {
            $billQuery = $database->table('bills as b')
                ->leftJoin('accounts as a', 'a.id', '=', 'b.party_account_id')
                ->leftJoin('items as i', 'i.id', '=', 'b.item_id')
                ->orderByDesc('b.bill_date')
                ->orderByDesc('b.id');

            if ($page !== 'bills.index') {
                $billQuery->where('b.bill_type', $this->billType($page));
            }

            $rows = $billQuery->limit(100)->get([
                'b.inv_no',
                'b.bill_date',
                'b.bill_type',
                'a.name_ur as party_name',
                'i.name_ur as item_name',
                'b.status',
            ]);
        } elseif (str_starts_with($page, 'vouchers.')) {
            $voucherType = substr($page, strlen('vouchers.'));
            $rows = $database->table('vouchers as v')
                ->leftJoin('accounts as a', 'a.id', '=', 'v.account_id')
                ->where('v.voucher_type', $voucherType)
                ->orderByDesc('v.voucher_date')
                ->orderByDesc('v.id')
                ->limit(100)
                ->get([
                    'v.inv_no',
                    'v.voucher_date',
                    'v.voucher_type',
                    'a.name_ur as account_name',
                    'v.amount',
                    'v.narration',
                ]);
        } elseif (in_array($page, ['reports.dailybook', 'reports.cash', 'ledger.party'], true)) {
            $entryQuery = $database->table('ledger_entries as e')
                ->join('ledger_txns as t', 't.id', '=', 'e.ledger_txn_id')
                ->join('accounts as a', 'a.id', '=', 'e.account_id')
                ->orderByDesc('t.entry_date')
                ->orderByDesc('e.id');

            if ($request->filled('from')) {
                $entryQuery->whereDate('t.entry_date', '>=', $request->date('from'));
            }

            if ($request->filled('to')) {
                $entryQuery->whereDate('t.entry_date', '<=', $request->date('to'));
            }

            if ($page === 'ledger.party') {
                $accountId = $request->integer('account_id');
                if ($accountId > 0) {
                    $entryQuery->where('e.account_id', $accountId);
                }
            }

            if ($page === 'reports.cash') {
                $entryQuery->join('account_roles as r', function ($join): void {
                    $join->on('r.account_id', '=', 'e.account_id')->where('r.role_key', '=', 'cash');
                });
            }

            $rows = $entryQuery->limit(200)->get([
                't.entry_date',
                'a.name_ur as account_name',
                't.narration',
                'e.debit',
                'e.credit',
            ]);
        } elseif (in_array($page, ['reports.balance', 'ledger.banam'], true)) {
            $balanceQuery = $database->table('accounts as a')
                ->join('account_types as t', 't.id', '=', 'a.account_type_id')
                ->leftJoin('ledger_entries as e', 'e.account_id', '=', 'a.id')
                ->select('a.code', 'a.name_ur', 't.name_ur as type_name')
                ->selectRaw('COALESCE(SUM(e.debit), 0) as total_debit')
                ->selectRaw('COALESCE(SUM(e.credit), 0) as total_credit')
                ->groupBy('a.id', 'a.code', 'a.name_ur', 't.name_ur')
                ->orderBy('a.code');

            if ($page === 'ledger.banam') {
                $balanceQuery->where('t.name_en', 'Customer');
            }

            $rows = $balanceQuery->get();
        } elseif ($page === 'reports.agrai') {
            $rows = $database->table('vouchers as v')
                ->leftJoin('accounts as a', 'a.id', '=', 'v.account_id')
                ->leftJoin('recovery_groups as g', 'g.id', '=', 'v.recovery_group_id')
                ->where('v.voucher_type', 'agrai')
                ->orderByDesc('v.voucher_date')
                ->orderByDesc('v.id')
                ->limit(200)
                ->get([
                    'v.inv_no',
                    'v.voucher_date',
                    'a.name_ur as account_name',
                    'g.name_ur as recovery_group',
                    'v.amount',
                    'v.narration',
                ]);
        } elseif ($page === 'stock.ledger') {
            $rows = $database->table('bill_lines as l')
                ->join('bills as b', 'b.id', '=', 'l.bill_id')
                ->leftJoin('items as i', 'i.id', '=', 'l.item_id')
                ->where('b.status', 'posted')
                ->orderByDesc('b.bill_date')
                ->orderByDesc('l.id')
                ->limit(200)
                ->get([
                    'b.inv_no',
                    'b.bill_date',
                    'i.name_ur as item_name',
                    'l.nag',
                    'l.wazan',
                    'l.rate',
                ]);
        }

        return view('portal.page', [
            'client' => $client,
            'page' => $page,
            'title' => $titles[$page] ?? 'پورٹل',
            'rows' => $rows,
            'accountTypes' => $accountTypes,
            'accounts' => $accounts,
            'items' => $items,
            'recoveryGroups' => $recoveryGroups,
            'settings' => $settings,
            'aiConfigured' => filled($client->ai_api_key),
            'postRoute' => $this->postRoute($page),
        ]);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $client = $this->client($request);
        $database = $this->databases->connection($client);
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:client_portal.accounts,code'],
            'account_type_id' => ['required', 'integer', 'exists:client_portal.account_types,id'],
            'recovery_group_id' => ['nullable', 'integer', 'exists:client_portal.recovery_groups,id'],
            'name_ur' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'commission_pct' => ['nullable', 'numeric', 'between:0,100'],
            'default_lagana' => ['nullable', 'numeric', 'min:0'],
        ]);

        $database->table('accounts')->insert([
            ...$validated,
            'commission_pct' => $validated['commission_pct'] ?? 0,
            'default_lagana' => $validated['default_lagana'] ?? 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('client.portal.accounts.index', ['slug' => $client->slug])
            ->with('success', 'کھاتہ کامیابی سے محفوظ ہوگیا۔');
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $client = $this->client($request);
        $database = $this->databases->connection($client);
        $validated = $request->validate([
            'name_ur' => ['required', 'string', 'max:255', 'unique:client_portal.items,name_ur'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'default_commission_pct' => ['nullable', 'numeric', 'between:0,100'],
            'default_mazdoori_rate' => ['nullable', 'numeric', 'min:0'],
            'default_market_fee_rate' => ['nullable', 'numeric', 'min:0'],
            'default_munshiana_rate' => ['nullable', 'numeric', 'min:0'],
            'default_lagana' => ['nullable', 'numeric', 'min:0'],
        ]);

        $database->table('items')->insert([
            ...$validated,
            'default_commission_pct' => $validated['default_commission_pct'] ?? 0,
            'default_mazdoori_rate' => $validated['default_mazdoori_rate'] ?? 0,
            'default_market_fee_rate' => $validated['default_market_fee_rate'] ?? 0,
            'default_munshiana_rate' => $validated['default_munshiana_rate'] ?? 0,
            'default_lagana' => $validated['default_lagana'] ?? 10,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('client.portal.items.create', ['slug' => $client->slug])
            ->with('success', 'جنس کامیابی سے محفوظ ہوگئی۔');
    }

    public function storeBill(Request $request): RedirectResponse
    {
        $client = $this->client($request);
        $database = $this->databases->connection($client);
        $billType = (int) $request->route('billType');
        $documentType = match ($billType) {
            1 => 'sale_maal',
            2 => 'khareed',
            3 => 'customer_kharid',
            default => abort(404),
        };
        $validated = $request->validate([
            'bill_date' => ['required', 'date'],
            'party_account_id' => ['nullable', 'integer', 'exists:client_portal.accounts,id'],
            'customer_account_id' => ['required', 'integer', 'exists:client_portal.accounts,id'],
            'item_id' => ['required', 'integer', 'exists:client_portal.items,id'],
            'nag' => ['required', 'numeric', 'min:0'],
            'wazan' => ['nullable', 'numeric', 'min:0'],
            'rate' => ['required', 'numeric', 'min:0'],
            'marka' => ['nullable', 'string', 'max:255'],
            'vehicle_no' => ['nullable', 'string', 'max:255'],
            'container_no' => ['nullable', 'string', 'max:255'],
        ]);

        $database->transaction(function () use ($database, $validated, $billType, $documentType): void {
            $invoiceNumber = $this->nextDocumentNumber($database, 'bill:'.$documentType);
            $billId = $database->table('bills')->insertGetId([
                'inv_no' => $invoiceNumber,
                'bill_type' => $billType,
                'bill_date' => $validated['bill_date'],
                'party_account_id' => $validated['party_account_id'] ?? null,
                'item_id' => $validated['item_id'],
                'marka' => $validated['marka'] ?? null,
                'vehicle_no' => $validated['vehicle_no'] ?? null,
                'container_no' => $validated['container_no'] ?? null,
                'lot_nag' => $validated['nag'],
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $database->table('bill_lines')->insert([
                'bill_id' => $billId,
                'customer_account_id' => $validated['customer_account_id'],
                'item_id' => $validated['item_id'],
                'line_date' => $validated['bill_date'],
                'nag' => $validated['nag'],
                'wazan' => $validated['wazan'] ?? null,
                'rate' => $validated['rate'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'بل بطور زیرِ التوا محفوظ ہوگیا۔');
    }

    public function storeVoucher(Request $request): RedirectResponse
    {
        $client = $this->client($request);
        $database = $this->databases->connection($client);
        $voucherType = (string) $request->route('voucherType');
        if (! in_array($voucherType, ['debit', 'credit', 'agrai', 'jv'], true)) {
            abort(404);
        }

        $validated = $request->validate([
            'voucher_date' => ['required', 'date'],
            'account_id' => [$voucherType === 'jv' ? 'nullable' : 'required', 'integer', 'exists:client_portal.accounts,id'],
            'recovery_group_id' => [$voucherType === 'agrai' ? 'required' : 'nullable', 'integer', 'exists:client_portal.recovery_groups,id'],
            'debit_account_id' => [$voucherType === 'jv' ? 'required' : 'nullable', 'integer', 'exists:client_portal.accounts,id'],
            'credit_account_id' => [$voucherType === 'jv' ? 'required' : 'nullable', 'integer', 'different:debit_account_id', 'exists:client_portal.accounts,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'narration' => ['nullable', 'string', 'max:255'],
        ]);

        $cashAccountId = $database->table('account_roles')
            ->where('role_key', 'cash')
            ->value('account_id');

        if (! $cashAccountId) {
            $cashAccountId = $database->table('accounts as a')
                ->join('account_types as t', 't.id', '=', 'a.account_type_id')
                ->where('t.name_en', 'Cash')
                ->where('a.is_active', true)
                ->value('a.id');
        }

        if ($voucherType !== 'jv' && ! $cashAccountId) {
            throw ValidationException::withMessages([
                'account_id' => 'واؤچر سے پہلے ایک فعال Cash اکاؤنٹ بنائیں۔',
            ]);
        }

        $database->transaction(function () use ($database, $validated, $voucherType, $cashAccountId): void {
            $invoiceNumber = $this->nextDocumentNumber($database, 'voucher:'.$voucherType);
            $voucherId = $database->table('vouchers')->insertGetId([
                'inv_no' => $invoiceNumber,
                'voucher_type' => $voucherType,
                'voucher_date' => $validated['voucher_date'],
                'account_id' => $voucherType === 'jv' ? null : $validated['account_id'],
                'amount' => $validated['amount'],
                'recovery_group_id' => $validated['recovery_group_id'] ?? null,
                'narration' => $validated['narration'] ?? null,
                'status' => 'posted',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $transactionId = $database->table('ledger_txns')->insertGetId([
                'idempotency_key' => (string) Str::uuid(),
                'source_type' => 'voucher',
                'source_id' => $voucherId,
                'entry_date' => $validated['voucher_date'],
                'narration' => $validated['narration'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($voucherType === 'jv') {
                $entries = [
                    ['account_id' => $validated['debit_account_id'], 'debit' => $validated['amount'], 'credit' => 0],
                    ['account_id' => $validated['credit_account_id'], 'debit' => 0, 'credit' => $validated['amount']],
                ];

                foreach ($entries as $entry) {
                    $database->table('voucher_lines')->insert([
                        'voucher_id' => $voucherId,
                        ...$entry,
                        'narration' => $validated['narration'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } elseif ($voucherType === 'debit') {
                $entries = [
                    ['account_id' => $validated['account_id'], 'debit' => $validated['amount'], 'credit' => 0],
                    ['account_id' => $cashAccountId, 'debit' => 0, 'credit' => $validated['amount']],
                ];
            } else {
                $entries = [
                    ['account_id' => $cashAccountId, 'debit' => $validated['amount'], 'credit' => 0],
                    ['account_id' => $validated['account_id'], 'debit' => 0, 'credit' => $validated['amount']],
                ];
            }

            foreach ($entries as $entry) {
                $database->table('ledger_entries')->insert([
                    'ledger_txn_id' => $transactionId,
                    ...$entry,
                    'narration' => $validated['narration'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'واؤچر محفوظ اور پوسٹ ہوگیا۔');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $client = $this->client($request);
        $database = $this->databases->connection($client);
        $validated = $request->validate([
            'firm_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($validated as $key => $value) {
            $setting = $database->table('settings')->where('key', $key)->first();

            if ($setting) {
                $database->table('settings')->where('id', $setting->id)->update([
                    'value' => $value,
                    'updated_at' => now(),
                ]);
            } else {
                $database->table('settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return back()->with('success', 'سیٹنگز محفوظ ہوگئیں۔');
    }

    public function updateAiSettings(Request $request): RedirectResponse
    {
        $client = $this->client($request);
        $validated = $request->validate([
            'ai_api_key' => ['nullable', 'string', 'max:255'],
            'remove_ai_api_key' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_ai_api_key')) {
            $client->ai_api_key = null;
        } elseif (filled($validated['ai_api_key'] ?? null)) {
            $client->ai_api_key = $validated['ai_api_key'];
        }

        $client->save();

        return back()->with('success', 'AI سیٹنگز محفوظ ہوگئیں۔');
    }

    public function chat(Request $request): JsonResponse
    {
        $client = $this->client($request);
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        if (blank($client->ai_api_key)) {
            return response()->json(['reply' => 'AI استعمال کرنے کے لیے پہلے سیٹنگز میں API key درج کریں۔'], 422);
        }

        try {
            $response = Http::withToken($client->ai_api_key)
                ->acceptJson()
                ->timeout(30)
                ->post(config('services.openai.endpoint'), [
                    'model' => config('services.openai.model'),
                    'messages' => [
                        ['role' => 'user', 'content' => $validated['message']],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Client AI provider connection failed.', ['client_id' => $client->id]);

            return response()->json(['reply' => 'AI سروس سے رابطہ نہیں ہوسکا۔'], 502);
        }

        if (! $response->successful()) {
            Log::warning('Client AI provider rejected a request.', [
                'client_id' => $client->id,
                'status' => $response->status(),
            ]);

            return response()->json(['reply' => 'AI سروس نے درخواست قبول نہیں کی۔'], 502);
        }

        $reply = $response->json('choices.0.message.content');
        if (! is_string($reply) || trim($reply) === '') {
            Log::warning('Client AI provider returned an empty response.', ['client_id' => $client->id]);

            return response()->json(['reply' => 'AI سروس سے جواب موصول نہیں ہوا۔'], 502);
        }

        return response()->json(['reply' => $reply]);
    }

    private function client(Request $request): Client
    {
        $client = $request->attributes->get('clientPortalClient');

        abort_unless($client instanceof Client, 404);

        return $client;
    }

    private function billType(string $page): int
    {
        return match ($page) {
            'bills.sale' => 1,
            'bills.khareed' => 2,
            'bills.customer' => 3,
            default => abort(404),
        };
    }

    private function nextDocumentNumber(Connection $database, string $documentType): string
    {
        $sequence = $database->table('doc_sequences')
            ->where('doc_type', $documentType)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            throw ValidationException::withMessages([
                'document' => 'اس دستاویز کی نمبرنگ ترتیب موجود نہیں۔',
            ]);
        }

        $database->table('doc_sequences')
            ->where('id', $sequence->id)
            ->update([
                'current_number' => $sequence->current_number + 1,
                'updated_at' => now(),
            ]);

        return $sequence->prefix.$sequence->current_number;
    }

    private function postRoute(string $page): ?string
    {
        return match ($page) {
            'accounts.create' => 'client.portal.accounts.store',
            'items.create' => 'client.portal.items.store',
            'bills.sale' => 'client.portal.bills.sale.store',
            'bills.khareed' => 'client.portal.bills.khareed.store',
            'bills.customer' => 'client.portal.bills.customer.store',
            'vouchers.debit' => 'client.portal.vouchers.debit.store',
            'vouchers.credit' => 'client.portal.vouchers.credit.store',
            'vouchers.agrai' => 'client.portal.vouchers.agrai.store',
            'vouchers.jv' => 'client.portal.vouchers.jv.store',
            'settings' => 'client.portal.settings.update',
            'ai-settings' => 'client.portal.ai-settings.update',
            default => null,
        };
    }
}
