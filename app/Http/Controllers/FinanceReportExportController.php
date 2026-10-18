<?php

namespace App\Http\Controllers;

use App\Services\FinanceReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportExportController extends Controller
{
    private const ROLES = ['admin', 'super_admin', 'cashier', 'casher', 'billing_staff', 'accountant'];

    public function csv(Request $request): StreamedResponse
    {
        [$start, $end] = $this->dates($request);
        $rows = FinanceReportService::rows(FinanceReportService::payments($start, $end));

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }, "finance-report-{$start}-to-{$end}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function excel(Request $request): StreamedResponse
    {
        [$start, $end] = $this->dates($request);
        $rows = FinanceReportService::rows(FinanceReportService::payments($start, $end));

        return response()->streamDownload(function () use ($rows): void {
            echo '<?xml version="1.0"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Finance Report"><Table>';
            foreach ($rows as $row) {
                echo '<Row>';
                foreach ($row as $index => $value) {
                    $type = $index === 5 && is_numeric($value) ? 'Number' : 'String';
                    echo '<Cell><Data ss:Type="' . $type . '">' . e((string) $value) . '</Data></Cell>';
                }
                echo '</Row>';
            }
            echo '</Table></Worksheet></Workbook>';
        }, "finance-report-{$start}-to-{$end}.xls", ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    public function pdf(Request $request)
    {
        [$start, $end] = $this->dates($request);
        $payments = FinanceReportService::payments($start, $end);
        $rows = FinanceReportService::rows($payments);
        $summary = FinanceReportService::summary($payments);
        $byMode = FinanceReportService::byPaymentMode($payments);
        $byService = FinanceReportService::byService($payments);

        return Pdf::loadView('finance.reports.pdf', compact('rows', 'start', 'end', 'summary', 'byMode', 'byService'))
            ->setPaper('a4', 'landscape')
            ->stream("finance-report-{$start}-to-{$end}.pdf");
    }

    /** @return array{string, string} */
    private function dates(Request $request): array
    {
        abort_unless($request->user()?->hasAnyRole(self::ROLES), 403);
        $validated = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        return [$validated['start_date'], $validated['end_date']];
    }
}
