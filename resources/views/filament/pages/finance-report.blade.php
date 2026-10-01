<x-filament-panels::page>
    <form method="GET" class="mb-6 grid gap-4 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-900 sm:grid-cols-3 sm:items-end">
        <div>
            <label for="start_date" class="fi-fo-field-wrp-label">Start date</label>
            <input id="start_date" name="start_date" type="date" value="{{ $startDate }}" class="fi-input mt-1 block w-full">
        </div>
        <div>
            <label for="end_date" class="fi-fo-field-wrp-label">End date</label>
            <input id="end_date" name="end_date" type="date" value="{{ $endDate }}" class="fi-input mt-1 block w-full">
        </div>
        <div>
            <button type="submit" class="fi-btn fi-btn-size-md fi-color-primary w-full">Apply dates</button>
        </div>
    </form>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl bg-emerald-700 p-5 text-white shadow-sm">
            <div class="text-sm opacity-80">Total recorded</div>
            <div class="mt-1 text-2xl font-bold">ETB {{ number_format($summary['total'], 2) }}</div>
            <div class="mt-1 text-xs opacity-80">All payment modes</div>
        </div>
        <div class="rounded-xl bg-teal-700 p-5 text-white shadow-sm">
            <div class="text-sm opacity-80">Patient collected</div>
            <div class="mt-1 text-2xl font-bold">ETB {{ number_format($summary['collected'], 2) }}</div>
            <div class="mt-1 text-xs opacity-80">Cash, card, and digital</div>
        </div>
        <div class="rounded-xl bg-blue-700 p-5 text-white shadow-sm">
            <div class="text-sm opacity-80">Insurance</div>
            <div class="mt-1 text-2xl font-bold">ETB {{ number_format($summary['insurance'], 2) }}</div>
            <div class="mt-1 text-xs opacity-80">Approved insurance payments</div>
        </div>
        <div class="rounded-xl bg-slate-700 p-5 text-white shadow-sm">
            <div class="text-sm opacity-80">Transactions</div>
            <div class="mt-1 text-2xl font-bold">{{ number_format($summary['transactions']) }}</div>
            <div class="mt-1 text-xs opacity-80">{{ $startDate }} to {{ $endDate }}</div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
            <h2 class="text-lg font-semibold">By payment mode</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead><tr class="border-b"><th class="py-2">Mode</th><th class="py-2">Transactions</th><th class="py-2 text-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse($byMode as $mode => $data)
                            <tr class="border-b last:border-0"><td class="py-2">{{ $mode }}</td><td class="py-2">{{ $data['count'] }}</td><td class="py-2 text-right">ETB {{ number_format($data['amount'], 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-center text-gray-500">No payments in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
            <h2 class="text-lg font-semibold">By service</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead><tr class="border-b"><th class="py-2">Service</th><th class="py-2">Transactions</th><th class="py-2 text-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse($byService as $service => $data)
                            <tr class="border-b last:border-0"><td class="py-2">{{ $service }}</td><td class="py-2">{{ $data['count'] }}</td><td class="py-2 text-right">ETB {{ number_format($data['amount'], 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-center text-gray-500">No payments in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="mt-6 rounded-xl bg-white p-5 shadow-sm dark:bg-gray-900">
        <h2 class="text-lg font-semibold">Invoices</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead><tr class="border-b"><th class="py-2">Invoice</th><th class="py-2">Patient</th><th class="py-2">Service</th><th class="py-2">Mode</th><th class="py-2">Cashier</th><th class="py-2 text-right">Amount</th><th class="py-2">Receipt</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr class="border-b last:border-0">
                            <td class="py-2 font-medium">{{ $payment->invoice_number }}</td>
                            <td class="py-2">{{ $payment->patient?->full_name ?? 'Unknown' }}</td>
                            <td class="py-2">{{ $payment->serviceRequest?->service?->name ?? 'Unknown' }}</td>
                            <td class="py-2">{{ ucfirst($payment->payment_mode) }}</td>
                            <td class="py-2">{{ $payment->collector?->name ?? 'Cashier' }}</td>
                            <td class="py-2 text-right">ETB {{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="py-2"><a class="text-primary-600 hover:underline" target="_blank" href="{{ route('receipts.payments.show', $payment) }}">Open</a> <a class="ml-2 text-primary-600 hover:underline" target="_blank" href="{{ route('payments.receipts.download', $payment) }}">PDF</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-4 text-center text-gray-500">No payments in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-filament-panels::page>
