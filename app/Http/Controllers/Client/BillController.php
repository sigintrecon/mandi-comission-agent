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

class BillController extends Controller
{
    // charge_heads name_en keys — fixed order matches legacy
    private const CHARGE_KEYS = [
        'commission' => 'Commission',
        'karaya' => 'Karaya',
        'kharch' => 'Kharch',
        'mazdoori' => 'Mazdoori',
        'market' => 'Market Fee',
        'munshiana' => 'Munshiana',
        'stor_karaya' => 'Store Rent',
        'naqdi' => 'Cash Payment',
    ];

    public function __construct(private ClientPortalDatabase $databases) {}

    // ── GET /{slug}/bills/sale ────────────────────────────
    public function create(Request $request): View
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:50'],
        ]);
        $billSearch = trim($validated['search'] ?? '');

        $db = $this->databases->connection($client);

        // Next invoice number from doc_sequences
        $seq = $db->table('doc_sequences')
            ->where('doc_type', 'bill:sale_maal')
            ->first(['current_number', 'prefix']);
        $nextInv = $seq ? ($seq->prefix.$seq->current_number) : '1';

        // Party accounts (بیوپاری type)
        $parties = $db->table('accounts as a')
            ->join('account_types as t', 't.id', '=', 'a.account_type_id')
            ->where('t.name_en', 'Party')
            ->where('a.is_active', true)
            ->orderBy('a.name_ur')
            ->get(['a.id', 'a.code', 'a.name_ur', 'a.name_en',
                'a.commission_pct', 'a.city']);

        // Customer accounts (گاہک type)
        $customers = $db->table('accounts as a')
            ->join('account_types as t', 't.id', '=', 'a.account_type_id')
            ->where('t.name_en', 'Customer')
            ->where('a.is_active', true)
            ->orderBy('a.name_ur')
            ->get(['a.id', 'a.code', 'a.name_ur', 'a.name_en', 'a.city']);

        // Marfat — same as Customer accounts (used as reference)
        $marfatList = $customers;

        // Items (جنس)
        $items = $db->table('items')
            ->where('is_active', true)
            ->orderBy('name_ur')
            ->get(['id', 'code', 'name_ur', 'name_en',
                'default_commission_pct', 'default_mazdoori_rate',
                'default_market_fee_rate', 'default_munshiana_rate',
                'default_lagana']);

        // charge_heads (for rates lookup)
        $chargeHeads = $db->table('charge_heads')
            ->orderBy('sort_order')
            ->get(['id', 'name_en', 'calc_type']);

        // Show recent bills by default and every matching bill for a search.
        $recentBills = $db->table('bills')
            ->where('bill_type', 1)
            ->when($billSearch !== '', fn ($query) => $query->where('inv_no', 'like', '%'.$billSearch.'%'))
            ->orderByDesc('id')
            ->when($billSearch === '', fn ($query) => $query->limit(50))
            ->get(['id', 'inv_no', 'bill_date', 'status', 'lot_nag']);

        $billItemNames = $db->table('bill_lines as lines')
            ->join('items', 'items.id', '=', 'lines.item_id')
            ->whereIn('lines.bill_id', $recentBills->pluck('id'))
            ->orderBy('lines.sort_order')
            ->get(['lines.bill_id', 'items.name_ur'])
            ->groupBy('bill_id')
            ->map(fn ($items) => $items->pluck('name_ur')->unique()->implode('، '));
        $recentBills->each(function ($bill) use ($billItemNames): void {
            $bill->item_names = $billItemNames->get($bill->id, '');
        });

        $editableBill = null;
        if ($request->filled('edit')) {
            $editableBill = $db->table('bills')
                ->where('bill_type', 1)
                ->where('id', $request->integer('edit'))
                ->first();

            abort_unless($editableBill, 404);
        } elseif ($billSearch !== '') {
            $editableBill = $db->table('bills')
                ->where('bill_type', 1)
                ->where(function ($query) use ($billSearch, $recentBills): void {
                    $query->where('inv_no', $billSearch);
                    if ($recentBills->count() === 1) {
                        $query->orWhere('id', $recentBills->first()->id);
                    }
                })
                ->first();
        }

        if ($editableBill) {
            $editableBill->lines = $db->table('bill_lines as lines')
                ->leftJoin('accounts as customers', 'customers.id', '=', 'lines.customer_account_id')
                ->leftJoin('items', 'items.id', '=', 'lines.item_id')
                ->where('lines.bill_id', $editableBill->id)
                ->orderBy('lines.sort_order')
                ->get([
                    'lines.line_date', 'lines.customer_account_id', 'customers.code as customer_code',
                    'customers.name_ur as customer_name', 'lines.item_id', 'items.code as item_code',
                    'items.name_ur as item_name', 'lines.nag', 'lines.wazan', 'lines.rate',
                    'lines.lagana', 'lines.lagana_amount', 'lines.trader_rate',
                ]);
            $editableBill->charges = $db->table('bill_charges')
                ->join('charge_heads', 'charge_heads.id', '=', 'bill_charges.charge_head_id')
                ->where('bill_charges.bill_id', $editableBill->id)
                ->pluck('bill_charges.rate', 'charge_heads.name_en');
        }

        // Marka list (distinct from existing bills)
        $markaList = $db->table('bills')
            ->whereNotNull('marka')
            ->where('marka', '!=', '')
            ->distinct()
            ->orderBy('marka')
            ->pluck('marka');

        return view('portal.bills.sale', compact(
            'client', 'nextInv', 'parties', 'customers',
            'marfatList', 'items', 'chargeHeads', 'recentBills', 'markaList', 'billSearch', 'editableBill'
        ));
    }

    public function allBills(Request $request): View
    {
        return $this->billReport($request, false);
    }

    public function allSaleBills(Request $request): View
    {
        return $this->billReport($request, true);
    }

    private function billReport(Request $request, bool $saleOnly): View
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $filters = $this->validatedReportFilters($request);
        $db = $this->databases->connection($client);

        $bills = $db->table('bills as bills')
            ->leftJoin('accounts as parties', 'parties.id', '=', 'bills.party_account_id')
            ->leftJoin('accounts as marfat', 'marfat.id', '=', 'bills.marfat_account_id')
            ->when($saleOnly, fn ($query) => $query->where('bills.bill_type', 1))
            ->when($filters['from'] !== '', fn ($query) => $query->whereDate('bills.bill_date', '>=', $filters['from']))
            ->when($filters['to'] !== '', fn ($query) => $query->whereDate('bills.bill_date', '<=', $filters['to']))
            ->when($filters['invoice'] !== '', fn ($query) => $query->where('bills.inv_no', 'like', '%'.$filters['invoice'].'%'))
            ->when($filters['status'] !== '', fn ($query) => $query->where('bills.status', $filters['status']))
            ->when($filters['customer_id'] !== '', function ($query) use ($filters): void {
                $query->whereExists(function ($lineQuery) use ($filters): void {
                    $lineQuery->selectRaw('1')
                        ->from('bill_lines as filter_lines')
                        ->whereColumn('filter_lines.bill_id', 'bills.id')
                        ->where('filter_lines.customer_account_id', $filters['customer_id']);
                });
            })
            ->when($filters['item_id'] !== '', function ($query) use ($filters): void {
                $query->where(function ($itemQuery) use ($filters): void {
                    $itemQuery->where('bills.item_id', $filters['item_id'])
                        ->orWhereExists(function ($lineQuery) use ($filters): void {
                            $lineQuery->selectRaw('1')
                                ->from('bill_lines as filter_lines')
                                ->whereColumn('filter_lines.bill_id', 'bills.id')
                                ->where('filter_lines.item_id', $filters['item_id']);
                        });
                });
            })
            ->orderByDesc('bills.bill_date')
            ->orderByDesc('bills.id')
            ->select([
                'bills.id', 'bills.inv_no', 'bills.bill_type', 'bills.bill_date',
                'bills.party_account_id', 'bills.marfat_account_id', 'bills.item_id',
                'bills.marka', 'bills.vehicle_no', 'bills.container_no',
                'bills.lot_nag', 'bills.status', 'parties.name_ur as party_name',
                'marfat.name_ur as marfat_name',
            ])
            ->paginate($filters['per_page'])
            ->withQueryString();

        $billIds = $bills->getCollection()->pluck('id');
        $lineGroups = $db->table('bill_lines as lines')
            ->leftJoin('accounts as customers', 'customers.id', '=', 'lines.customer_account_id')
            ->leftJoin('items', 'items.id', '=', 'lines.item_id')
            ->whereIn('lines.bill_id', $billIds)
            ->orderBy('lines.sort_order')
            ->get([
                'lines.bill_id', 'customers.name_ur as customer_name', 'customers.code as customer_code',
                'items.name_ur as item_name', 'items.code as item_code',
                'lines.nag', 'lines.wazan', 'lines.rate', 'lines.lagana',
                'lines.lagana_amount', 'lines.trader_rate', 'lines.cust_amount',
                'lines.party_amount', 'lines.diff_amount',
            ])
            ->groupBy('bill_id');
        $chargeGroups = $db->table('bill_charges')
            ->join('charge_heads', 'charge_heads.id', '=', 'bill_charges.charge_head_id')
            ->whereIn('bill_charges.bill_id', $billIds)
            ->orderBy('charge_heads.sort_order')
            ->get([
                'bill_charges.bill_id', 'charge_heads.name_en',
                'bill_charges.rate', 'bill_charges.amount',
            ])
            ->groupBy('bill_id');

        $bills->getCollection()->each(function ($bill) use ($lineGroups, $chargeGroups): void {
            $bill->lines = $lineGroups->get($bill->id, collect());
            $bill->charges = $chargeGroups->get($bill->id, collect());
        });

        $customers = $db->table('accounts as accounts')
            ->join('account_types as types', 'types.id', '=', 'accounts.account_type_id')
            ->where('types.name_en', 'Customer')
            ->orderBy('accounts.name_ur')
            ->get(['accounts.id', 'accounts.name_ur', 'accounts.code']);
        $items = $db->table('items')->orderBy('name_ur')->get(['id', 'name_ur', 'code']);
        $reportType = $saleOnly ? 'sale' : 'bills';

        return view('portal.bills.report', compact(
            'client', 'bills', 'customers', 'items', 'filters', 'reportType'
        ));
    }

    public function customerExplanation(Request $request): View
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $filters = $this->validatedReportFilters($request);
        $db = $this->databases->connection($client);

        $lines = $db->table('bill_lines as lines')
            ->join('bills', 'bills.id', '=', 'lines.bill_id')
            ->join('accounts as customers', 'customers.id', '=', 'lines.customer_account_id')
            ->leftJoin('accounts as parties', 'parties.id', '=', 'bills.party_account_id')
            ->leftJoin('items', 'items.id', '=', 'lines.item_id')
            ->where('bills.bill_type', 1)
            ->when($filters['from'] !== '', fn ($query) => $query->whereDate('bills.bill_date', '>=', $filters['from']))
            ->when($filters['to'] !== '', fn ($query) => $query->whereDate('bills.bill_date', '<=', $filters['to']))
            ->when($filters['invoice'] !== '', fn ($query) => $query->where('bills.inv_no', 'like', '%'.$filters['invoice'].'%'))
            ->when($filters['status'] !== '', fn ($query) => $query->where('bills.status', $filters['status']))
            ->when($filters['customer_id'] !== '', fn ($query) => $query->where('lines.customer_account_id', $filters['customer_id']))
            ->when($filters['item_id'] !== '', fn ($query) => $query->where('lines.item_id', $filters['item_id']))
            ->orderByDesc('bills.bill_date')
            ->orderByDesc('bills.id')
            ->orderBy('lines.sort_order')
            ->select([
                'lines.id', 'bills.inv_no', 'bills.bill_date', 'bills.status',
                'customers.name_ur as customer_name', 'customers.code as customer_code',
                'parties.name_ur as party_name', 'items.name_ur as item_name',
                'lines.nag', 'lines.wazan', 'lines.rate', 'lines.lagana',
                'lines.lagana_amount', 'lines.cust_amount', 'lines.party_amount',
                'lines.diff_amount',
            ])
            ->paginate($filters['per_page'])
            ->withQueryString();

        $customers = $db->table('accounts as accounts')
            ->join('account_types as types', 'types.id', '=', 'accounts.account_type_id')
            ->where('types.name_en', 'Customer')
            ->orderBy('accounts.name_ur')
            ->get(['accounts.id', 'accounts.name_ur', 'accounts.code']);
        $items = $db->table('items')->orderBy('name_ur')->get(['id', 'name_ur', 'code']);
        $reportType = 'customers';

        return view('portal.bills.report', compact(
            'client', 'lines', 'customers', 'items', 'filters', 'reportType'
        ));
    }

    // ── POST /{slug}/bills/sale ───────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $validated = $request->validate([
            'inv_no' => ['required', 'string', 'max:50'],
            'bill_id' => ['nullable', 'integer', 'min:1'],
            'bill_date' => ['required', 'date'],
            'party_account_id' => ['required', 'integer', 'min:1'],
            'marfat_account_id' => ['nullable', 'integer', 'min:1'],
            'item_id' => ['nullable', 'integer', 'min:1'],
            'marka' => ['nullable', 'string', 'max:100'],
            'vehicle_no' => ['nullable', 'string', 'max:50'],
            'container_no' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:pending,posted'],

            // Bill lines
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_date' => ['nullable', 'date'],
            'lines.*.customer_account_id' => ['required', 'integer', 'min:1'],
            'lines.*.item_id' => ['nullable', 'integer', 'min:1'],
            'lines.*.nag' => ['required', 'numeric', 'min:0'],
            'lines.*.wazan' => ['nullable', 'numeric', 'min:0'],
            'lines.*.rate' => ['required', 'numeric', 'min:0'],
            'lines.*.lagana' => ['nullable', 'numeric', 'min:0'],
            'lines.*.trader_rate' => ['nullable', 'numeric', 'min:0'],

            // Charges
            'charges' => ['nullable', 'array'],
            'charges.commission_pct' => ['nullable', 'numeric', 'min:0'],
            'charges.karaya' => ['nullable', 'numeric', 'min:0'],
            'charges.kharch' => ['nullable', 'numeric', 'min:0'],
            'charges.mazdoori' => ['nullable', 'numeric', 'min:0'],
            'charges.market' => ['nullable', 'numeric', 'min:0'],
            'charges.munshiana' => ['nullable', 'numeric', 'min:0'],
            'charges.stor_karaya' => ['nullable', 'numeric', 'min:0'],
            'charges.naqdi' => ['nullable', 'numeric', 'min:0'],
        ]);

        $db = $this->databases->connection($client);
        $billToUpdate = null;
        if (! empty($validated['bill_id'])) {
            $billToUpdate = $db->table('bills')
                ->where('bill_type', 1)
                ->where('id', $validated['bill_id'])
                ->first();

            abort_unless($billToUpdate, 404);
        }

        // ── Compute bill totals from lines ───────────────
        $totalNag = 0;
        $totalGross = 0;  // sum of party_amount (cust_amount when no trader_rate)
        $totalDiff = 0;

        $processedLines = [];
        foreach ($validated['lines'] as $i => $line) {
            $nag = (float) ($line['nag'] ?? 0);
            $wazan = (float) ($line['wazan'] ?? 0);
            $rate = (float) ($line['rate'] ?? 0);
            $lagana = (float) ($line['lagana'] ?? 0);
            $traderRate = isset($line['trader_rate']) && $line['trader_rate'] !== ''
                          ? (float) $line['trader_rate'] : null;

            // cust_amount = nag*(rate+lagana)  OR wazan*rate + nag*lagana
            if ($wazan > 0) {
                $custAmount = $wazan * $rate + $nag * $lagana;
            } else {
                $custAmount = $nag * ($rate + $lagana);
            }

            // party_amount = nag*traderRate  OR  custAmount if no traderRate
            if ($traderRate !== null && $traderRate > 0) {
                $partyAmount = ($wazan > 0) ? $wazan * $traderRate : $nag * $traderRate;
                $diffAmount = $nag * ($rate - $traderRate);
            } else {
                $partyAmount = ($wazan > 0) ? $wazan * $rate : $nag * $rate;
                $diffAmount = 0;
            }

            $laganaAmount = $nag * $lagana;

            $totalNag += $nag;
            $totalGross += $partyAmount;   // بیوپاری کو دینا ہے
            $totalDiff += $diffAmount;

            $processedLines[] = [
                'line_date' => isset($line['line_date']) && $line['line_date']
                                         ? Carbon::parse($line['line_date'])->toDateString()
                                         : Carbon::parse($validated['bill_date'])->toDateString(),
                'customer_account_id' => (int) $line['customer_account_id'],
                'item_id' => isset($line['item_id']) ? (int) $line['item_id'] : null,
                'nag' => $nag,
                'wazan' => $wazan > 0 ? $wazan : null,
                'rate' => $rate,
                'lagana' => $lagana,
                'trader_rate' => $traderRate,
                'cust_amount' => round($custAmount, 2),
                'party_amount' => round($partyAmount, 2),
                'lagana_amount' => round($laganaAmount, 2),
                'diff_amount' => round($diffAmount, 2),
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // ── Compute charge amounts ────────────────────────
        $chargesInput = $validated['charges'] ?? [];
        $commPct = (float) ($chargesInput['commission_pct'] ?? 0);
        $commAmount = round($totalGross * $commPct / 100, 2);

        // Per-nag charges
        $karayaRate = (float) ($chargesInput['karaya'] ?? 0);
        $kharchRate = (float) ($chargesInput['kharch'] ?? 0);
        $mazdooriRate = (float) ($chargesInput['mazdoori'] ?? 0);
        $marketRate = (float) ($chargesInput['market'] ?? 0);
        $munshiRate = (float) ($chargesInput['munshiana'] ?? 0);

        // Manual charges
        $storKaraya = (float) ($chargesInput['stor_karaya'] ?? 0);
        $naqdi = (float) ($chargesInput['naqdi'] ?? 0);

        $totalExpenses = $commAmount
            + $karayaRate
            + ($kharchRate * $totalNag)
            + ($mazdooriRate * $totalNag)
            + ($marketRate * $totalNag)
            + ($munshiRate * $totalNag)
            + $storKaraya
            + $naqdi;

        $safiAmount = $totalGross - $totalExpenses;

        // ── Insert bill (DB transaction) ──────────────────
        try {
            $db->beginTransaction();

            $billData = [
                'inv_no' => $validated['inv_no'],
                'bill_type' => 1,
                'bill_date' => Carbon::parse($validated['bill_date'])->toDateString(),
                'party_account_id' => (int) $validated['party_account_id'],
                'marfat_account_id' => isset($validated['marfat_account_id'])
                                       ? (int) $validated['marfat_account_id'] : null,
                'item_id' => isset($validated['item_id'])
                                       ? (int) $validated['item_id'] : null,
                'marka' => $validated['marka'] ?? null,
                'vehicle_no' => $validated['vehicle_no'] ?? null,
                'container_no' => $validated['container_no'] ?? null,
                'lot_nag' => $totalNag,
                'status' => $validated['status'],
                'updated_at' => now(),
            ];

            if ($billToUpdate) {
                $billId = $billToUpdate->id;
                $db->table('bills')->where('id', $billId)->update($billData);
                $db->table('bill_lines')->where('bill_id', $billId)->delete();
                $db->table('bill_charges')->where('bill_id', $billId)->delete();
            } else {
                $db->table('doc_sequences')
                    ->where('doc_type', 'bill:sale_maal')
                    ->increment('current_number');
                $billData['created_at'] = now();
                $billId = $db->table('bills')->insertGetId($billData);
            }

            // Insert bill lines
            foreach ($processedLines as &$ln) {
                $ln['bill_id'] = $billId;
            }
            $db->table('bill_lines')->insert($processedLines);

            // Insert bill_charges
            $chargeHeads = $db->table('charge_heads')
                ->whereIn('name_en', array_values(self::CHARGE_KEYS))
                ->get(['id', 'name_en'])
                ->keyBy('name_en');

            $chargeRows = [];
            $chargeMap = [
                'Commission' => ['rate' => $commPct,       'amount' => $commAmount],
                'Karaya' => ['rate' => $karayaRate,    'amount' => round($karayaRate, 2)],
                'Kharch' => ['rate' => $kharchRate,    'amount' => round($kharchRate * $totalNag, 2)],
                'Mazdoori' => ['rate' => $mazdooriRate,  'amount' => round($mazdooriRate * $totalNag, 2)],
                'Market Fee' => ['rate' => $marketRate,    'amount' => round($marketRate * $totalNag, 2)],
                'Munshiana' => ['rate' => $munshiRate,    'amount' => round($munshiRate * $totalNag, 2)],
                'Store Rent' => ['rate' => 0,              'amount' => $storKaraya],
                'Cash Payment' => ['rate' => 0,              'amount' => $naqdi],
            ];

            foreach ($chargeMap as $nameEn => $vals) {
                if (! isset($chargeHeads[$nameEn])) {
                    continue;
                }
                $chargeRows[] = [
                    'bill_id' => $billId,
                    'charge_head_id' => $chargeHeads[$nameEn]->id,
                    'rate' => $vals['rate'],
                    'amount' => $vals['amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($chargeRows)) {
                $db->table('bill_charges')->insert($chargeRows);
            }

            $db->commit();

        } catch (QueryException $e) {
            $db->rollBack();

            if (($e->errorInfo[1] ?? 0) === 1062
                || ($e->errorInfo[0] ?? '') === '23000') {
                return back()
                    ->withInput()
                    ->withErrors(['inv_no' => 'یہ انوائس نمبر پہلے سے موجود ہے۔']);
            }

            throw $e;
        }

        $msg = $billToUpdate
            ? 'سیل مال انوائس کامیابی سے اپ ڈیٹ ہوگئی۔'
            : ($validated['status'] === 'pending'
                ? 'بیجک نامکمل محفوظ ہوگیا۔'
                : 'سیل مال بل کامیابی سے محفوظ ہوگیا۔');

        $redirect = redirect()->route('client.portal.bills.sale', [
            'slug' => $client->slug,
            ...($billToUpdate ? ['search' => $validated['inv_no']] : []),
        ]);

        return $redirect
            ->with('success', $msg);
    }

    /**
     * @return array{from: string, to: string, customer_id: string, item_id: string, invoice: string, status: string, per_page: int}
     */
    private function validatedReportFilters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'customer_id' => ['nullable', 'integer', 'min:1'],
            'item_id' => ['nullable', 'integer', 'min:1'],
            'invoice' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:pending,posted'],
            'per_page' => ['nullable', 'in:10,25,50,100'],
        ]);

        return [
            'from' => $validated['from'] ?? '',
            'to' => $validated['to'] ?? '',
            'customer_id' => (string) ($validated['customer_id'] ?? ''),
            'item_id' => (string) ($validated['item_id'] ?? ''),
            'invoice' => trim($validated['invoice'] ?? ''),
            'status' => $validated['status'] ?? '',
            'per_page' => (int) ($validated['per_page'] ?? 10),
        ];
    }
}
