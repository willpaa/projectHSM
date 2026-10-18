<?php

namespace App\Filament\Pages;

use App\Services\FinanceReportService;
use Filament\Pages\Page;

class FinanceReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Finance Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.finance-report';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            'admin',
            'super_admin',
            'cashier',
            'casher',
            'billing_staff',
            'accountant',
        ]) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole([
            'admin',
            'super_admin',
            'cashier',
            'casher',
            'billing_staff',
            'accountant',
        ]) ?? false;
    }

    public function mount(): void
    {
        $this->startDate = request()->query('start_date', now()->startOfMonth()->toDateString());
        $this->endDate = request()->query('end_date', now()->toDateString());
    }

    public function getViewData(): array
    {
        $payments = FinanceReportService::payments($this->startDate, $this->endDate);

        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'payments' => $payments,
            'summary' => FinanceReportService::summary($payments),
            'byMode' => FinanceReportService::byPaymentMode($payments),
            'byService' => FinanceReportService::byService($payments),
            'csvUrl' => route('finance-reports.export.csv', ['start_date' => $this->startDate, 'end_date' => $this->endDate]),
            'excelUrl' => route('finance-reports.export.excel', ['start_date' => $this->startDate, 'end_date' => $this->endDate]),
            'pdfUrl' => route('finance-reports.export.pdf', ['start_date' => $this->startDate, 'end_date' => $this->endDate]),
        ];
    }

}
