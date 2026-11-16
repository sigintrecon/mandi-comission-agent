<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BillLedgerPoster
{
    public function post(Connection $database, int $billId): void
    {
        $bill = $database->table('bills')->where('id', $billId)->first();
        if (! $bill || ! in_array((int) $bill->bill_type, [1, 2], true)) {
            return;
        }

        $lines = $database->table('bill_lines')->where('bill_id', $billId)->get();
        $charges = $database->table('bill_charges as charges')
            ->join('charge_heads as heads', 'heads.id', '=', 'charges.charge_head_id')
            ->where('charges.bill_id', $billId)
            ->get([
                'heads.account_id', 'heads.name_en', 'charges.amount',
            ]);

        $entries = [];
        $partyAmount = 0.0;
        $laganaAmount = 0.0;
        $differenceAmount = 0.0;
        $expenseAmount = 0.0;
        $narration = 'Bill '.$bill->inv_no;

        foreach ($lines as $line) {
            $partyAmount += (float) $line->party_amount;
            $laganaAmount += (float) $line->lagana_amount;
            $differenceAmount += (float) $line->diff_amount;

            if ((int) $bill->bill_type === 1) {
                $this->appendEntry($entries, (int) $line->customer_account_id, (float) $line->cust_amount, 'debit', $narration);
            }
        }

        foreach ($charges as $charge) {
            $amount = (float) $charge->amount;
            $expenseAmount += $amount;
            $this->appendEntry($entries, (int) $charge->account_id, $amount, 'credit', $narration.' - '.$charge->name_en);
        }

        $safiAmount = round($partyAmount - $expenseAmount, 2);

        if ((int) $bill->bill_type === 1) {
            $this->appendSignedEntry($entries, (int) $bill->party_account_id, $safiAmount, 'credit', $narration.' - Safi');

            if ($laganaAmount > 0) {
                $laganaAccountId = $this->roleAccountId($database, 'lagana', '4009');
                $this->appendEntry($entries, $laganaAccountId, $laganaAmount, 'credit', $narration.' - Lagana');
            }

            if ($differenceAmount !== 0.0) {
                $differenceAccountId = $this->roleAccountId($database, 'difference', '4008');
                $this->appendEntry(
                    $entries,
                    $differenceAccountId,
                    abs($differenceAmount),
                    $differenceAmount > 0 ? 'credit' : 'debit',
                    $narration.' - Difference'
                );
            }
        } else {
            $purchaseAccountId = $this->roleAccountId($database, 'sale_purchase', '4011');
            $this->appendEntry($entries, $purchaseAccountId, $partyAmount, 'debit', $narration.' - Goods');
            $this->appendSignedEntry($entries, (int) $bill->party_account_id, $safiAmount, 'credit', $narration.' - Supplier');
        }

        $debitTotal = round(array_sum(array_column($entries, 'debit')), 2);
        $creditTotal = round(array_sum(array_column($entries, 'credit')), 2);

        if (abs($debitTotal - $creditTotal) > 0.01) {
            throw ValidationException::withMessages([
                'ledger' => 'اس بل کی رقم متوازن نہیں، محفوظ نہیں کیا گیا۔ اکاؤنٹس اور ریٹس چیک کریں۔',
            ]);
        }

        if ($entries === []) {
            return;
        }

        $transactionId = $database->table('ledger_txns')->insertGetId([
            'idempotency_key' => (string) Str::uuid(),
            'source_type' => 'bill',
            'source_id' => $billId,
            'entry_date' => $bill->bill_date,
            'narration' => $narration,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($entries as $entry) {
            $database->table('ledger_entries')->insert([
                'ledger_txn_id' => $transactionId,
                ...$entry,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function reverse(Connection $database, int $billId): void
    {
        $transactions = $database->table('ledger_txns')
            ->where('source_type', 'bill')
            ->where('source_id', $billId)
            ->get(['id', 'narration']);

        foreach ($transactions as $transaction) {
            $entries = $database->table('ledger_entries')
                ->where('ledger_txn_id', $transaction->id)
                ->get(['account_id', 'debit', 'credit', 'narration']);

            if ($entries->isEmpty()) {
                $database->table('ledger_txns')->where('id', $transaction->id)
                    ->update(['source_type' => 'bill_reversed', 'updated_at' => now()]);

                continue;
            }

            $reversalId = $database->table('ledger_txns')->insertGetId([
                'idempotency_key' => (string) Str::uuid(),
                'source_type' => 'bill_reversal',
                'source_id' => $billId,
                'entry_date' => now()->toDateString(),
                'narration' => 'Reversal: '.$transaction->narration,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($entries as $entry) {
                $database->table('ledger_entries')->insert([
                    'ledger_txn_id' => $reversalId,
                    'account_id' => $entry->account_id,
                    'debit' => $entry->credit,
                    'credit' => $entry->debit,
                    'narration' => 'Reversal: '.$entry->narration,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $database->table('ledger_txns')->where('id', $transaction->id)
                ->update(['source_type' => 'bill_reversed', 'updated_at' => now()]);
        }
    }

    private function roleAccountId(Connection $database, string $roleKey, string $fallbackCode): int
    {
        $accountId = $database->table('account_roles')->where('role_key', $roleKey)->value('account_id');
        if (! $accountId) {
            $accountId = $database->table('accounts')->where('code', $fallbackCode)->value('id');
        }

        if (! $accountId) {
            throw ValidationException::withMessages([
                'ledger' => 'اکاؤنٹ رول '.$roleKey.' سیٹ نہیں ہے۔ پہلے متعلقہ سسٹم اکاؤنٹ بنائیں۔',
            ]);
        }

        return (int) $accountId;
    }

    /** @param array<int, array{account_id:int,debit:float,credit:float,narration:string}> $entries */
    private function appendEntry(array &$entries, int $accountId, float $amount, string $side, string $narration): void
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            return;
        }

        $entries[] = [
            'account_id' => $accountId,
            'debit' => $side === 'debit' ? $amount : 0,
            'credit' => $side === 'credit' ? $amount : 0,
            'narration' => $narration,
        ];
    }

    /** @param array<int, array{account_id:int,debit:float,credit:float,narration:string}> $entries */
    private function appendSignedEntry(array &$entries, int $accountId, float $amount, string $normalSide, string $narration): void
    {
        $side = $amount < 0
            ? ($normalSide === 'credit' ? 'debit' : 'credit')
            : $normalSide;

        $this->appendEntry($entries, $accountId, abs($amount), $side, $narration);
    }
}
