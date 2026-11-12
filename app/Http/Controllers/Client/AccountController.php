<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientPortalDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(private ClientPortalDatabase $databases) {}

    public function index(Request $request): View
    {
        return $this->create($request);
    }

    public function create(Request $request): View
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $db = $this->databases->connection($client);

        $accountTypes = $db->table('account_types')
            ->orderByRaw("CASE WHEN name_en = 'Customer' THEN 0 ELSE 1 END")
            ->orderBy('name_en')
            ->get();
        $recoveryGroups = $db->table('recovery_groups')
            ->whereIn('name_en', ['New', 'Dead'])
            ->orderByRaw("CASE WHEN name_en = 'New' THEN 0 ELSE 1 END")
            ->get();

        // Account codes are stored as strings, so cast them before finding the numeric maximum.
        $lastCode = $db->table('accounts')
            ->selectRaw('MAX(CAST(code AS UNSIGNED)) as max_code')
            ->value('max_code');
        $nextCode = $lastCode ? ((int) preg_replace('/\D/', '', $lastCode) + 1) : 4001;

        // Cities list from existing accounts
        $cities = $db->table('accounts')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        // Existing accounts list (latest first, limit 200)
        $accounts = $db->table('accounts as a')
            ->join('account_types as t', 't.id', '=', 'a.account_type_id')
            ->leftJoin('recovery_groups as r', 'r.id', '=', 'a.recovery_group_id')
            ->orderByDesc('a.id')
            ->limit(200)
            ->get([
                'a.id',
                'a.code',
                'a.name_ur',
                'a.name_en',
                'a.city',
                'a.phone',
                'a.commission_pct',
                'a.account_type_id',
                'a.recovery_group_id',
                't.name_en as type_en',
                't.name_ur as type_ur',
                'r.name_en as recovery_group',
                'a.opening_debit',
                'a.opening_credit',
                'a.created_at',
            ]);

        return view('portal.accounts.create', compact(
            'client', 'accountTypes', 'recoveryGroups',
            'nextCode', 'cities', 'accounts'
        ));
    }

    public function lookup(Request $request)
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);
        $code = trim($validated['code']);

        $account = $this->databases->connection($client)
            ->table('accounts')
            ->where('code', $code)
            ->first([
                'id',
                'code',
                'account_type_id',
                'recovery_group_id',
                'name_ur',
                'name_en',
                'city',
                'phone',
                'commission_pct',
                'bank_details',
                'opening_credit',
                'opening_debit',
                'created_at',
            ]);

        if ($account !== null) {
            $account->created_date = substr((string) $account->created_at, 0, 10);
            $account->opening_balance_side = (float) $account->opening_credit > 0
                ? 'jama'
                : ((float) $account->opening_debit > 0 ? 'banam' : 'jama');
            $account->opening_balance = (float) $account->opening_credit > 0
                ? $account->opening_credit
                : $account->opening_debit;
            unset($account->opening_credit, $account->opening_debit);
        }

        return response()->json(['account' => $account]);
    }

    public function store(Request $request): RedirectResponse
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $db = $this->databases->connection($client);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'created_date' => ['nullable', 'date'],
            'account_type_id' => ['required', 'integer'],
            'recovery_group_id' => ['nullable', 'integer'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'name_ur' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'commission_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bank_details' => ['nullable', 'string', 'max:500'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'opening_balance_side' => ['required', 'in:jama,banam'],
        ]);
        $validated['code'] = trim($validated['code']);
        $openingBalance = $validated['opening_balance'] ?? 0;

        $accountData = [
            'code' => $validated['code'],
            'account_type_id' => (int) $validated['account_type_id'],
            'recovery_group_id' => ! empty($validated['recovery_group_id']) ? (int) $validated['recovery_group_id'] : null,
            'name_en' => $validated['name_en'] ?? null,
            'name_ur' => $validated['name_ur'],
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'commission_pct' => $validated['commission_pct'] ?? 0,
            'bank_details' => $validated['bank_details'] ?? null,
            'opening_credit' => $validated['opening_balance_side'] === 'jama' ? $openingBalance : 0,
            'opening_debit' => $validated['opening_balance_side'] === 'banam' ? $openingBalance : 0,
            'created_at' => isset($validated['created_date'])
                ? Carbon::parse($validated['created_date'])->startOfDay()
                : now(),
            'updated_at' => now(),
        ];

        $existingAccount = $db->table('accounts')
            ->where('code', $validated['code'])
            ->first(['id']);

        if ($existingAccount !== null) {
            $db->table('accounts')
                ->where('id', $existingAccount->id)
                ->update($accountData);

            return redirect()
                ->route('client.portal.accounts.create', ['slug' => $client->slug])
                ->with('success', 'کھاتے کی تفصیلات کامیابی سے اپ ڈیٹ ہوگئیں۔');
        }

        try {
            $db->table('accounts')->insert([
                ...$accountData,
                'is_active' => true,
            ]);
        } catch (QueryException $exception) {
            if (! $db->table('accounts')->where('code', $validated['code'])->exists()) {
                throw $exception;
            }

            return back()
                ->withErrors(['code' => 'یہ پارٹی نمبر پہلے سے موجود ہے۔'])
                ->withInput();
        }

        return redirect()
            ->route('client.portal.accounts.create', ['slug' => $client->slug])
            ->with('success', 'کھاتہ کامیابی سے محفوظ ہوگیا۔');
    }

    public function destroy(Request $request, string $slug, string $account): RedirectResponse
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $db = $this->databases->connection($client);
        $accounts = $db->table('accounts');

        abort_unless($accounts->where('id', $account)->exists(), 404);

        try {
            $deleted = $accounts->where('id', $account)->delete();
        } catch (QueryException $exception) {
            if (! $this->isForeignKeyConstraintViolation($exception)) {
                throw $exception;
            }

            return redirect()
                ->route('client.portal.accounts.create', ['slug' => $client->slug])
                ->withErrors([
                    'account' => 'یہ کھاتہ پہلے سے لین دین یا سسٹم کی ترتیبات میں استعمال ہو رہا ہے، اس لیے حذف نہیں کیا جا سکتا۔',
                ]);
        }

        abort_unless($deleted > 0, 404);

        return redirect()
            ->route('client.portal.accounts.create', ['slug' => $client->slug])
            ->with('success', 'کھاتہ کامیابی سے حذف ہوگیا۔');
    }

    private function isForeignKeyConstraintViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? (string) $exception->getCode();
        $driverCode = $exception->errorInfo[1] ?? null;

        return $sqlState === '23503'
            || $driverCode === 1451
            || ($driverCode === 19 && str_contains($exception->getMessage(), 'FOREIGN KEY constraint failed'));
    }
}
