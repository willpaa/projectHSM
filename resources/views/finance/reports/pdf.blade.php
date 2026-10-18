<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 10px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .period { color: #64748b; margin-bottom: 20px; }
        h2 { font-size: 13px; margin: 18px 0 8px; }
        .summary { width: 100%; margin-bottom: 14px; }
        .summary td { width: 25%; background: #f8fafc; font-size: 11px; }
        .summary strong { display: block; margin-top: 5px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #e8f3f1; text-align: left; }
        th, td { padding: 7px; border: 1px solid #d8e0e8; }
        tr:nth-child(even) { background: #f8fafc; }
        .breakdowns { width: 100%; }
        .breakdowns > tbody > tr > td { width: 50%; vertical-align: top; border: 0; padding: 0 10px 0 0; }
        .breakdowns table { margin-bottom: 8px; }
        .amount { text-align: right; white-space: nowrap; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>Finance Report</h1>
    <div class="period">{{ $start }} to {{ $end }}</div>
    <table class="summary">
        <tbody><tr>
            <td>Total recorded<strong>ETB {{ number_format($summary['total'], 2) }}</strong></td>
            <td>Patient collected<strong>ETB {{ number_format($summary['collected'], 2) }}</strong></td>
            <td>Insurance<strong>ETB {{ number_format($summary['insurance'], 2) }}</strong></td>
            <td>Transactions<strong>{{ number_format($summary['transactions']) }}</strong></td>
        </tr></tbody>
    </table>

    <table class="breakdowns">
        <tbody><tr>
            <td>
                <h2>By payment mode</h2>
                <table>
                    <thead><tr><th>Mode</th><th>Transactions</th><th class="amount">Amount</th></tr></thead>
                    <tbody>
                        @forelse ($byMode as $mode => $data)
                            <tr><td>{{ $mode }}</td><td>{{ $data['count'] }}</td><td class="amount">ETB {{ number_format($data['amount'], 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3">No payments in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td>
                <h2>By service</h2>
                <table>
                    <thead><tr><th>Service</th><th>Transactions</th><th class="amount">Amount</th></tr></thead>
                    <tbody>
                        @forelse ($byService as $service => $data)
                            <tr><td>{{ $service }}</td><td>{{ $data['count'] }}</td><td class="amount">ETB {{ number_format($data['amount'], 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3">No payments in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr></tbody>
    </table>

    <h2>Invoices</h2>
    <table>
        <thead><tr>@foreach ($rows[0] as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach (array_slice($rows, 1) as $row)
                <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
