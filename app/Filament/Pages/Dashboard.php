<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\HospitalAppointmentsPieChart;
use App\Filament\Widgets\HospitalIncomeExpenseChart;
use App\Filament\Widgets\HospitalReportsTypeChart;
use App\Filament\Widgets\HospitalStatsOverview;
use App\Filament\Widgets\CashierOverview;
use App\Filament\Widgets\CashierPaymentModeChart;
use App\Filament\Widgets\CashierQueue;
use App\Filament\Widgets\RoleBillingOverview;
use App\Filament\Widgets\RoleBillingStatusChart;
use App\Filament\Widgets\RoleDoctorWorkloadOverview;
use App\Filament\Widgets\RoleDoctorLabResults;
use App\Filament\Widgets\RoleDoctorRadiologyResults;
use App\Filament\Widgets\RoleDoctorWeeklyAppointmentsChart;
use App\Filament\Widgets\RoleOperationsDepartmentChart;
use App\Filament\Widgets\RolePharmacyOverview;
use App\Filament\Widgets\RolePharmacyReportOverview;
use App\Filament\Widgets\RolePharmacyQueue;
use App\Filament\Widgets\RolePharmacySalesMixChart;
use App\Filament\Widgets\RolePharmacyExpiringStock;
use App\Filament\Widgets\RolePatientFlowOverview;
use App\Filament\Widgets\RolePatientAppointmentsTrendChart;
use App\Filament\Widgets\RolePatientSelfServiceOverview;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Str;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return $this->getDynamicDashboardTitle();
    }

    public function getHeading(): string
    {
        return $this->getDynamicDashboardTitle();
    }

    public function getWidgets(): array
    {
        $role = $this->getCurrentRole();

        if (in_array($role, ['admin', 'super_admin'], true)) {
            return $this->includePharmacyReportForAuthorizedUser([
                HospitalStatsOverview::class,
                CashierOverview::class,
                RolePharmacyReportOverview::class,
               // CashierQueue::class,
                CashierPaymentModeChart::class,
                HospitalIncomeExpenseChart::class,
                HospitalAppointmentsPieChart::class,
                HospitalReportsTypeChart::class,
            ]);
        }

        // A user may have multiple roles, and their first stored role may not be doctor.
        if (auth()->user()?->hasRole('doctor')) {
            $role = 'doctor';
        }

        if ($role === 'doctor') {
            return $this->includePharmacyReportForAuthorizedUser([
                RoleDoctorWorkloadOverview::class,
                RoleDoctorLabResults::class,
                RoleDoctorRadiologyResults::class,
                RoleDoctorWeeklyAppointmentsChart::class,
                HospitalAppointmentsPieChart::class,
                HospitalReportsTypeChart::class,
            ]);
        }

        if (in_array($role, ['nurse', 'triage_nurse'], true)) {
            return $this->includePharmacyReportForAuthorizedUser([
                RoleDoctorWorkloadOverview::class,
                RoleDoctorWeeklyAppointmentsChart::class,
                HospitalAppointmentsPieChart::class,
                HospitalReportsTypeChart::class,
            ]);
        }

        if (in_array($role, ['opd_staff', 'ipd_staff', 'receptionist'], true)) {
            return $this->includePharmacyReportForAuthorizedUser([
                RolePatientFlowOverview::class,
                RoleOperationsDepartmentChart::class,
                HospitalAppointmentsPieChart::class,
                HospitalIncomeExpenseChart::class,
            ]);
        }

        if (in_array($role, ['cashier', 'casher', 'billing_staff', 'accountant'], true)) {
            return $this->includePharmacyReportForAuthorizedUser([
                CashierOverview::class,
                CashierQueue::class,
                CashierPaymentModeChart::class,
            ]);
        }

        if (in_array($role, ['lab_staff', 'laboratory_staff', 'lab_technician', 'radiology_staff', 'radiology_technician'], true)) {
            return $this->includePharmacyReportForAuthorizedUser([
                RolePatientFlowOverview::class,
                RoleOperationsDepartmentChart::class,
                HospitalReportsTypeChart::class,
                HospitalStatsOverview::class,
            ]);
        }

        $user = auth()->user();

        // Older staff accounts were created with the misspelled role `pharmcy`.
        if ($user?->hasAnyRole(['pharmacy_staff', 'pharmacist', 'pharmacy', 'pharmcy'])) {
            return $this->includePharmacyReportForAuthorizedUser([
                RolePharmacyOverview::class,
                RolePharmacySalesMixChart::class,
                RolePharmacyReportOverview::class,
                RolePharmacyQueue::class,
                RolePharmacyExpiringStock::class,
            ]);
        }

        if ($role === 'patient') {
            return $this->includePharmacyReportForAuthorizedUser([
                RolePatientSelfServiceOverview::class,
                RolePatientAppointmentsTrendChart::class,
            ]);
        }

        return $this->includePharmacyReportForAuthorizedUser([
            HospitalStatsOverview::class,
            HospitalAppointmentsPieChart::class,
        ]);
    }

    /** @param array<class-string> $widgets
     *  @return array<class-string>
     */
    private function includePharmacyReportForAuthorizedUser(array $widgets): array
    {
        $user = auth()->user();
        $canViewReport = $user?->hasAnyRole([
            'admin',
            'super_admin',
            'pharmacy_staff',
            'pharmacist',
            'pharmacy',
            'pharmcy',
        ]) ?? false;

        if ($canViewReport && ! in_array(RolePharmacyReportOverview::class, $widgets, true)) {
            $widgets[] = RolePharmacyReportOverview::class;
        }

        return $widgets;
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    protected function getDynamicDashboardTitle(): string
    {
        $user = auth()->user();

        if (! $user) {
            return 'Hospital Dashboard';
        }

        return match (true) {
            $user->hasAnyRole(['admin', 'super_admin']) => 'Hospital Administration Dashboard',
            $user->hasAnyRole(['doctor']) => 'Doctor Dashboard',
            $user->hasAnyRole(['nurse', 'triage_nurse']) => 'Nursing Dashboard',
            $user->hasAnyRole(['opd_staff', 'ipd_staff', 'receptionist']) => 'Patient Operations Dashboard',
            $user->hasAnyRole(['cashier', 'casher', 'billing_staff', 'accountant']) => 'Cashier Dashboard',
            $user->hasAnyRole(['lab_staff', 'laboratory_staff', 'lab_technician']) => 'Laboratory Dashboard',
            $user->hasAnyRole(['radiology_staff', 'radiology_technician']) => 'Radiology Dashboard',
            $user->hasAnyRole(['pharmacy_staff', 'pharmacist', 'pharmacy', 'pharmcy']) => 'Pharmacy Management Dashboard',
            $user->hasAnyRole(['patient']) => 'Patient Dashboard',
            default => 'Hospital Dashboard',
        };
    }

    protected function getCurrentRole(): string
    {
        $role = auth()->user()?->roles?->first()?->name;

        return Str::of((string) ($role ?? 'user'))->lower()->value();
    }
}
