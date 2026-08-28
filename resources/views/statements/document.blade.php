@php
    $money = fn ($value) => number_format((float) $value, 2);
    $date = fn ($value) => $value ? \Carbon\CarbonImmutable::parse($value)->format('d.m.Y') : '';

    /*
     * DomPDF can corrupt a single table when tall, wrapped rows land exactly
     * on a page boundary. Group rows by their estimated wrapped line count so
     * each page gets a fresh table and repeated headings.
     */
    $statementPages = [];
    $currentPage = [];
    $currentWeight = 0;
    $pageCapacity = 32;

    foreach ($transactions as $row) {
        $descriptionLength = mb_strlen(trim((string) ($row['description'] ?? '')));
        $referenceLength = mb_strlen(trim((string) ($row['text'] ?? '')));
        $rowWeight = max(
            2,
            (int) ceil($descriptionLength / 18),
            (int) ceil($referenceLength / 18)
        ) + 1;

        if ($currentPage !== [] && $currentWeight + $rowWeight > $pageCapacity) {
            $statementPages[] = $currentPage;
            $currentPage = [];
            $currentWeight = 0;
            $pageCapacity = 72;
        }

        $currentPage[] = $row;
        $currentWeight += $rowWeight;
    }

    if ($currentPage !== [] || $statementPages === []) {
        $statementPages[] = $currentPage;
    }
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>{!! file_get_contents(public_path('css/statement.css')) !!}</style>
</head>
<body>
    <div class="page">
        <table class="company-header">
            <tr>
                <td class="company-details">
                    <div class="company-name">{{ $company['name'] }}</div>
                    @if($company['address'])<div>{{ $company['address'] }}</div>@endif
                    @if($company['phone'])<div>{{ $company['phone'] }}</div>@endif
                </td>
                <td class="document-label">CUSTOMER ACCOUNT STATEMENT<br><span>(NAIRA)</span></td>
            </tr>
        </table>

        <table class="period-row">
            <tr><td><strong>Period:</strong> {{ $date($period['from']) }} &nbsp; To &nbsp; {{ $date($period['to']) }}</td></tr>
        </table>

        <table class="information-grid">
            <tr>
                <td class="issued-to">
                    <div class="section-heading">Issued To:</div>
                    <div class="issued-to-details">
                        @foreach([$customer['customer_code'], $customer['customer_name'], $customer['address'], $customer['city']] as $issuedToLine)
                            @if(trim((string) $issuedToLine) !== '')
                                <div class="issued-to-line">{{ $issuedToLine }}</div>
                            @endif
                        @endforeach
                    </div>
                </td>
                <td class="summary-cell">
                    <table class="summary-table">
                        <tr><td class="summary-label">Opening Balance</td><td class="summary-separator">:</td><td class="summary-value">{{ $money($summary['opening_balance']) }}</td></tr>
                        <tr><td class="summary-label">Credit</td><td class="summary-separator">:</td><td class="summary-value">{{ $money($summary['credit']) }}</td></tr>
                        <tr><td class="summary-label">Debit</td><td class="summary-separator">:</td><td class="summary-value">{{ $money($summary['debit']) }}</td></tr>
                        <tr class="closing-row"><td class="summary-label">Closing Balance</td><td class="summary-separator">:</td><td class="summary-value">{{ $money($summary['closing_balance']) }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        @foreach($statementPages as $pageIndex => $pageTransactions)
            <div class="statement-page {{ !$loop->last ? 'statement-page-break' : '' }}">
            <table class="statement-table">
                <thead>
                    <tr class="table-caption-row"><th colspan="6">Customer Account Statement{{ $pageIndex > 0 ? ' (continued)' : '' }}</th></tr>
                    <tr class="column-headings">
                        <th class="date-column">Post. Date</th>
                        <th class="type-column">Type</th>
                        <th class="reference-column">Reference</th>
                        <th class="amount-column">Debit</th>
                        <th class="amount-column">Credit</th>
                        <th class="balance-column">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @if($pageIndex === 0)
                        <tr class="opening-balance-row">
                            <td colspan="3"></td>
                            <td colspan="2" class="opening-label">OPENING BALANCE</td>
                            <td class="number">{{ $money($summary['opening_balance']) }}</td>
                        </tr>
                    @endif
                    @foreach($pageTransactions as $row)
                        @php($contentLength = max(mb_strlen((string) ($row['description'] ?? '')), mb_strlen((string) ($row['text'] ?? ''))))
                        <tr class="{{ $contentLength > 240 ? 'dense-row' : '' }}">
                            <td class="date">{{ $date($row['posting_date']) }}</td>
                            <td class="type">{{ $row['description'] }}</td>
                            <td class="reference">{{ $row['text'] }}</td>
                            <td class="number">{{ $money($row['debit']) }}</td>
                            <td class="number">{{ $money($row['credit']) }}</td>
                            <td class="number">{{ $money($row['balance']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endforeach

        <div class="footer-note">This is a computer-generated statement and requires no signature.</div>
    </div>
</body>
</html>
