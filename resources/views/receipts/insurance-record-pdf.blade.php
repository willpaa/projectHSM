<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Insurance Settlement Record — {{ $payment->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #172033; font-size: 12px; }
        header { border-bottom: 3px solid #2563eb; padding: 0 0 14px; }
        h1 { margin: 0 0 6px; font-size: 22px; }
        .notice { margin: 20px 0; padding: 12px; background: #fff7ed; border: 1px solid #fdba74; color: #9a3412; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; }
        td:first-child { color: #64748b; width: 38%; }
        .amount { margin-top: 22px; padding: 16px; background: #eff6ff; font-size: 17px; font-weight: bold; }
        footer { margin-top: 28px; color: #64748b; font-size: 10px; }
    </style>
</head>
<body>
    <header><h1>{{ config('app.name', 'Hospital') }}</h1><div>Insurance coverage approval record</div></header>
    <div class="notice">This is an internal insurance record, not a receipt for money paid by the patient. Confirm actual settlement with the insurer separately.</div>
    <table>
        <tr><td>Record / invoice number</td><td>{{ $payment->invoice_number }}</td></tr>
        <tr><td>Recorded on</td><td>{{ $payment->payment_date?->format('d M Y, h:i A') ?? '—' }}</td></tr>
        <tr><td>Patient</td><td>{{ $payment->patient?->full_name ?? '—' }} ({{ $payment->patient?->patient_id ?? '—' }})</td></tr>
        <tr><td>Insurance provider</td><td>{{ $payment->patient?->insurance?->provider_name ?? '—' }}</td></tr>
        <tr><td>Policy number</td><td>{{ $payment->patient?->insurance?->policy_no ?? '—' }}</td></tr>
        <tr><td>Claim reference</td><td>{{ $payment->patient?->insurance?->claim_id ?? '—' }}</td></tr>
        <tr><td>Service</td><td>{{ $payment->serviceRequest?->service?->name ?? '—' }}</td></tr>
        <tr><td>Service request</td><td>{{ $payment->serviceRequest?->request_number ?? '—' }}</td></tr>
        <tr><td>Recorded by</td><td>{{ $payment->collector?->name ?? '—' }}</td></tr>
    </table>
    <div class="amount">Insurance amount recorded: ETB {{ number_format((float) $payment->amount, 2) }}</div>
    <footer>{{ $payment->remarks ?: 'Manually recorded by hospital finance. Confirm insurer approval and settlement against the insurer statement.' }}</footer>
</body>
</html>
