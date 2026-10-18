<?php

namespace App\Http\Controllers;

use App\Services\PharmacyReportService;
use Illuminate\Contracts\View\View;

class PharmacyReportPrintController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(request()->user()?->hasAnyRole([
            'admin',
            'super_admin',
            'pharmacy_staff',
            'pharmacist',
            'pharmacy',
            'pharmcy',
        ]), 403);

        $startDate = request('start_date');
        $endDate = request('end_date');
        $report = PharmacyReportService::build($startDate, $endDate);

        return view('pharmacy.reports.print', [
            'startDate' => optional($report['start'])->format('Y-m-d'),
            'endDate' => optional($report['end'])->format('Y-m-d'),
            'summary' => $report['summary'],
            'recentTransactions' => $report['recent_transactions'],
            'recentSales' => $report['recent_sales'],
            'topMedicines' => $report['top_medicines'],
        ]);
    }
}
