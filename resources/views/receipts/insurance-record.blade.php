<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Insurance Settlement Record — {{ $payment->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #172033; margin: 40px auto; max-width: 780px; }
        header { border-bottom: 3px solid #2563eb; padding-bottom: 18px; }
        h1 { margin: 0 0 8px; font-size: 25px; }
        .notice { margin: 22px 0; padding: 14px; background: #fff7ed; border: 1px solid #fdba74; color: #9a3412; font-weight: 700; }
        dl { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 30px; }
        dt { color: #64748b; font-size: 13px; }
        dd { margin: 4px 0 0; font-weight: 600; }
        .amount { margin-top: 24px; padding: 18px; background: #eff6ff; font-size: 20px; font-weight: 700; }
        footer { margin-top: 35px; color: #64748b; font-size: 12px; }
        @media print { body { margin: 12mm auto; } }
    </style>
</head>
<body>
    <header>
        <h1>{{ config('app.name', 'Hospital') }}</h1>
        <div>Insurance coverage approval record</div>
    </header>
    <div class="notice">This is an internal insurance record, not a receipt for money paid by the patient. Confirm actual settlement with the insurer separately.</div>
    <dl>
        <div><dt>Record / invoice number</dt><dd>{{ $payment->invoice_number }}</dd></div>
        <div><dt>Recorded on</dt><dd>{{ $payment->payment_date?->format('d M Y, h:i A') ?? '—' }}</dd></div>
        <div><dt>Patient</dt><dd>{{ $payment->patient?->full_name ?? '—' }}</dd></div>
        <div><dt>Patient ID</dt><dd>{{ $payment->patient?->patient_id ?? '—' }}</dd></div>
        <div><dt>Insurance provider</dt><dd>{{ $payment->patient?->insurance?->provider_name ?? '—' }}</dd></div>
        <div><dt>Policy number</dt><dd>{{ $payment->patient?->insurance?->policy_no ?? '—' }}</dd></div>
        <div><dt>Claim reference</dt><dd>{{ $payment->patient?->insurance?->claim_id ?? '—' }}</dd></div>
        <div><dt>Service</dt><dd>{{ $payment->serviceRequest?->service?->name ?? '—' }}</dd></div>
        <div><dt>Service request</dt><dd>{{ $payment->serviceRequest?->request_number ?? '—' }}</dd></div>
        <div><dt>Recorded by</dt><dd>{{ $payment->collector?->name ?? '—' }}</dd></div>
    </dl>
    <div class="amount">Insurance amount recorded: ETB {{ number_format((float) $payment->amount, 2) }}</div>
    <footer>{{ $payment->remarks ?: 'Manually recorded by hospital finance. Confirm insurer approval and settlement against the insurer statement.' }}</footer>
</body>
</html>
