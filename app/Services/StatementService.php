<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class StatementService
{
    public function organizations(): array
    {
        return DB::table('snapshot_customers')
            ->select('sales_org')
            ->selectRaw('COUNT(DISTINCT sold_to_party) AS customer_count')
            ->whereNotNull('sold_to_party')
            ->where('sold_to_party', '<>', '')
            ->groupBy('sales_org')
            ->orderBy('sales_org')
            ->get()->map(fn ($row) => (array) $row)->all();
    }

    public function customers(string $salesOrg, string $search = '')
    {
        return DB::table('snapshot_customers')
            ->select('sold_to_party')
            ->selectRaw('MAX(customer_name) AS customer_name, MAX(address) AS address, MAX(state) AS state, MAX(channel) AS channel')
            ->whereRaw('LOWER(sales_org) = LOWER(?)', [$salesOrg])
            ->whereNotNull('sold_to_party')->where('sold_to_party', '<>', '')
            ->when($search !== '', function ($query) use ($search) {
                $term = "%{$search}%";
                $query->where(function ($query) use ($term) {
                    $query->where('sold_to_party', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhere('address', 'like', $term);
                });
            })
            ->groupBy('sold_to_party')
            ->orderBy('customer_name')->orderBy('sold_to_party')
            ->simplePaginate(100)->withQueryString();
    }

    public function statement(string $salesOrg, string $soldToParty, string $from, string $to): array
    {
        $periodStart = CarbonImmutable::parse($from);
        $openingBalance = (float) DB::table('customer_balance')
            ->whereRaw('LOWER(sales_org) = LOWER(?)', [$salesOrg])
            ->where('customer_code', $soldToParty)
            ->whereRaw('CONCAT(fiscalyear, permonth) < ?', [$periodStart->format('Ym')])
            ->sum('local_balance');

        $rows = DB::table('customer_statement')
            ->select('sales_org', 'posting_date', 'document_no', 'item_no', 'description', 'text', 'dc', 'amount')
            ->whereRaw('LOWER(sales_org) = LOWER(?)', [$salesOrg])
            ->where('customer_code', $soldToParty)
            ->whereBetween('posting_date', [$from, $to])
            ->where('amount', '<>', 0)
            ->orderBy('posting_date')->orderBy('document_no')->orderBy('item_no')
            ->get()->map(fn ($row) => (array) $row)->all();

        $balance = $openingBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($rows as &$row) {
            $rawAmount = (float) $row['amount'];
            $amount = abs($rawAmount);
            $dc = strtoupper(trim((string) ($row['dc'] ?? '')));
            if ($dc === '') {
                $dc = $rawAmount < 0 ? 'D' : 'C';
            }
            $row['debit'] = $dc === 'D' ? $amount : 0.0;
            $row['credit'] = $dc === 'D' ? 0.0 : $amount;
            if ($dc === 'D') {
                $totalDebit += $amount;
            } else {
                $totalCredit += $amount;
            }
            // Running balance follows the signed source amount independently
            // of the debit/credit column used for presentation.
            $balance += $rawAmount;
            $row['balance'] = $balance;
        }
        unset($row);

        $customer = (array) (DB::table('snapshot_customers')
            ->select('sold_to_party', 'customer_name', 'address', 'lga', 'state', 'email')
            ->whereRaw('LOWER(sales_org) = LOWER(?)', [$salesOrg])
            ->where('sold_to_party', $soldToParty)
            ->orderByRaw('(customer = sold_to_party) DESC')->orderBy('customer')->first() ?? (object) []);

        return [
            'company' => $this->company($salesOrg),
            'customer' => [
                'customer_code' => $soldToParty,
                'customer_name' => $customer['customer_name'] ?? '',
                'address' => $customer['address'] ?? '',
                'city' => implode(', ', array_filter([$customer['lga'] ?? '', $customer['state'] ?? ''])),
                'email' => $customer['email'] ?? '',
            ],
            'period' => compact('from', 'to'),
            'summary' => ['opening_balance' => $openingBalance, 'credit' => $totalCredit, 'debit' => $totalDebit, 'closing_balance' => $balance],
            'transactions' => $rows,
        ];
    }

    private function company(string $salesOrg): array
    {
        return match (strtolower(trim($salesOrg))) {
            'pfnl' => ['name' => 'Primera Food Nigeria Limited', 'address' => 'Agbara, Ogun State, Nigeria', 'phone' => '', 'email' => ''],
            'emanl' => ['name' => 'Euro Mega Atlantic Nigeria Limited', 'address' => '1 Henry Carr St, Oba Akran, Lagos 102212, Lagos', 'phone' => '0907 604 3277', 'email' => ''],
            default => ['name' => 'Unknown Company', 'address' => '', 'phone' => '', 'email' => ''],
        };
    }
}
