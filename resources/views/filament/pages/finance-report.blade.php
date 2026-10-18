<x-filament-panels::page>
    <form method="GET" class="mb-6 grid gap-4 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-900 sm:grid-cols-5 sm:items-end">
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
        <div class="flex flex-wrap gap-2 sm:col-span-5">
            <a href="{{ $csvUrl }}" class="fi-btn fi-btn-size-md fi-color-gray">Download CSV</a>
            <a href="{{ $excelUrl }}" class="fi-btn fi-btn-size-md fi-color-success">Download Excel</a>
            <a href="{{ $pdfUrl }}" target="_blank" class="fi-btn fi-btn-size-md fi-color-danger">View PDF</a>
        </div>
    </form>

    <section class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-900">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
            <h2 class="text-lg font-semibold">Financial summary</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $startDate }} to {{ $endDate }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Report item</th>
                        <th scope="col" class="px-5 py-3 text-right font-semibold">Amount / count</th>
                        <th scope="col" class="px-5 py-3">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <th scope="row" class="px-5 py-3 font-medium">Total recorded</th>
                        <td class="px-5 py-3 text-right font-semibold">ETB {{ number_format($summary['total'], 2) }}</td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">All payment modes</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-3 font-medium">Patient collected</th>
                        <td class="px-5 py-3 text-right font-semibold">ETB {{ number_format($summary['collected'], 2) }}</td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">Cash, card, and digital</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-3 font-medium">Insurance</th>
                        <td class="px-5 py-3 text-right font-semibold">ETB {{ number_format($summary['insurance'], 2) }}</td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">Insurance payments</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-3 font-medium">Transactions</th>
                        <td class="px-5 py-3 text-right font-semibold">{{ number_format($summary['transactions']) }}</td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">Payments in selected period</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

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
