<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientPortalDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function __construct(private ClientPortalDatabase $databases) {}

    public function create(Request $request): View
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $db = $this->databases->connection($client);

        $lastCode = $db->table('items')
            ->selectRaw('MAX(CAST(code AS UNSIGNED)) as max_code')
            ->value('max_code');
        $nextCode = $lastCode ? ((int) $lastCode + 1) : 1;

        $items = $db->table('items')
            ->orderByDesc('id')
            ->limit(200)
            ->get([
                'id', 'code', 'name_en', 'name_ur',
                'default_commission_pct', 'default_mazdoori_rate',
                'default_market_fee_rate', 'default_munshiana_rate',
                'default_lagana', 'is_active',
            ]);

        return view('portal.items.create', compact('client', 'nextCode', 'items'));
    }

    public function lookup(Request $request)
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $validated = $request->validate(['code' => ['required', 'integer', 'min:1']]);

        $item = $this->databases->connection($client)
            ->table('items')
            ->where('code', (int) $validated['code'])
            ->first([
                'id', 'code', 'name_en', 'name_ur',
                'default_commission_pct', 'default_mazdoori_rate',
                'default_market_fee_rate', 'default_munshiana_rate',
                'default_lagana',
            ]);

        return response()->json(['item' => $item]);
    }

    public function store(Request $request): RedirectResponse
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $db = $this->databases->connection($client);

        $validated = $request->validate([
            'code'                     => ['required', 'integer', 'min:1'],
            'name_en'                  => ['nullable', 'string', 'max:255'],
            'name_ur'                  => ['required', 'string', 'max:255'],
            'default_commission_pct'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_mazdoori_rate'    => ['nullable', 'numeric', 'min:0'],
            'default_market_fee_rate'  => ['nullable', 'numeric', 'min:0'],
            'default_munshiana_rate'   => ['nullable', 'numeric', 'min:0'],
            'default_lagana'           => ['nullable', 'numeric', 'min:0'],
        ]);

        $data = [
            'code'                    => (int) $validated['code'],
            'name_en'                 => $validated['name_en'] ?? null,
            'name_ur'                 => $validated['name_ur'],
            'default_commission_pct'  => $validated['default_commission_pct']  ?? 0,
            'default_mazdoori_rate'   => $validated['default_mazdoori_rate']   ?? 0,
            'default_market_fee_rate' => $validated['default_market_fee_rate'] ?? 0,
            'default_munshiana_rate'  => $validated['default_munshiana_rate']  ?? 0,
            'default_lagana'          => $validated['default_lagana']          ?? 0,
            'updated_at'              => now(),
        ];

        $existingByCode = $db->table('items')
            ->where('code', (int) $validated['code'])
            ->first(['id']);

        if ($existingByCode !== null) {
            $db->table('items')->where('id', $existingByCode->id)->update($data);

            return redirect()
                ->route('client.portal.items.create', ['slug' => $client->slug])
                ->with('success', 'جنس کی تفصیلات کامیابی سے اپ ڈیٹ ہوگئیں۔');
        }

        $existingByName = $db->table('items')
            ->where('name_ur', $validated['name_ur'])
            ->first(['id', 'code']);

        if ($existingByName !== null) {
            return back()
                ->withErrors(['name_ur' => 'یہ جنس (نام اردو) پہلے سے موجود ہے — کوڈ: ' . $existingByName->code])
                ->withInput();
        }

        try {
            $db->table('items')->insert([
                ...$data,
                'is_active'  => true,
                'created_at' => now(),
            ]);
        } catch (QueryException $e) {
            if ($this->isDuplicateEntry($e)) {
                return back()
                    ->withErrors(['code' => 'یہ کوڈ یا جنس پہلے سے موجود ہے۔'])
                    ->withInput();
            }
            throw $e;
        }

        return redirect()
            ->route('client.portal.items.create', ['slug' => $client->slug])
            ->with('success', 'جنس کامیابی سے محفوظ ہوگئی۔');
    }

    public function destroy(Request $request, string $slug, string $item): RedirectResponse
    {
        $client = $request->attributes->get('clientPortalClient');
        abort_unless($client instanceof Client, 404);

        $db = $this->databases->connection($client);

        abort_unless($db->table('items')->where('id', $item)->exists(), 404);

        try {
            $deleted = $db->table('items')->where('id', $item)->delete();
        } catch (QueryException $e) {
            if (! $this->isForeignKeyViolation($e)) {
                throw $e;
            }

            return redirect()
                ->route('client.portal.items.create', ['slug' => $client->slug])
                ->withErrors(['item' => 'یہ جنس بلوں میں استعمال ہو رہی ہے، اس لیے حذف نہیں ہو سکتی۔']);
        }

        abort_unless($deleted > 0, 404);

        return redirect()
            ->route('client.portal.items.create', ['slug' => $client->slug])
            ->with('success', 'جنس کامیابی سے حذف ہوگئی۔');
    }

    private function isDuplicateEntry(QueryException $e): bool
    {
        $sqlState   = $e->errorInfo[0] ?? (string) $e->getCode();
        $driverCode = $e->errorInfo[1] ?? null;
        return $sqlState === '23000' || $driverCode === 1062;
    }

    private function isForeignKeyViolation(QueryException $e): bool
    {
        $sqlState   = $e->errorInfo[0] ?? (string) $e->getCode();
        $driverCode = $e->errorInfo[1] ?? null;
        return $sqlState === '23503'
            || $driverCode === 1451
            || ($driverCode === 19 && str_contains($e->getMessage(), 'FOREIGN KEY constraint failed'));
    }
}