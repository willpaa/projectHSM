<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\HospitalAppointmentsPieChart;
use App\Filament\Widgets\HospitalIncomeExpenseChart;
use App\Filament\Widgets\HospitalReportsTypeChart;
use App\Filament\Widgets\HospitalStatsOverview;
use App\Filament\Widgets\CashierOverview;
use App\Filament\Widgets\CashierPaymentModeChart;
use App\Filament\Widgets\CashierQueue;
use App\Filament\Widgets\RoleClinicalTechnicianOverview;
use App\Filament\Widgets\RoleDoctorWorkloadOverview;
use App\Filament\Widgets\RoleDoctorLabResults;
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
use App\Filament\Widgets\RoleNurseOverview;
use App\Filament\Widgets\RoleTriageNurseOverview;
use Filament\Pages\Dashboard as BaseDashboard;

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

        return match ($role) {
            'admin' => [
                HospitalStatsOverview::class,
                CashierOverview::class,
                RolePharmacyReportOverview::class,
                CashierPaymentModeChart::class,
                HospitalIncomeExpenseChart::class,
                HospitalAppointmentsPieChart::class,
                HospitalReportsTypeChart::class,
            ],
            'doctor' => [
                RoleDoctorWorkloadOverview::class,
                RoleDoctorLabResults::class,
                RoleDoctorWeeklyAppointmentsChart::class,
            ],
            'nurse' => [RoleNurseOverview::class],
            'triage_nurse' => [RoleTriageNurseOverview::class],
            'operations' => [
                RolePatientFlowOverview::class,
                RoleOperationsDepartmentChart::class,
                HospitalAppointmentsPieChart::class,
                HospitalIncomeExpenseChart::class,
            ],
            'finance' => [
                CashierOverview::class,
                CashierQueue::class,
                CashierPaymentModeChart::class,
            ],
            'laboratory', 'radiology' => [RoleClinicalTechnicianOverview::class],
            'pharmacy' => [
                RolePharmacyOverview::class,
                RolePharmacySalesMixChart::class,
                RolePharmacyReportOverview::class,
                RolePharmacyQueue::class,
                RolePharmacyExpiringStock::class,
            ],
            'patient' => [
                RolePatientSelfServiceOverview::class,
                RolePatientAppointmentsTrendChart::class,
            ],
            default => [],
        };
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
        return match ($this->getCurrentRole()) {
            'admin' => 'Hospital Administration Dashboard',
            'doctor' => 'Doctor Dashboard',
            'nurse' => 'Nursing Dashboard',
            'triage_nurse' => 'Triage Nurse Dashboard',
            'operations' => 'Patient Operations Dashboard',
            'finance' => 'Finance Dashboard',
            'laboratory' => 'Laboratory Dashboard',
            'radiology' => 'Radiology Dashboard',
            'pharmacy' => 'Pharmacy Management Dashboard',
            'patient' => 'Patient Dashboard',
            default => 'Account Dashboard',
        };
    }

    protected function getCurrentRole(): string
    {
        $user = auth()->user();

        if (! $user) {
            return 'user';
        }

        return match (true) {
            $user->hasAnyRole(['admin', 'super_admin']) => 'admin',
            $user->hasRole('doctor') => 'doctor',
            $user->hasAnyRole(['pharmacy_staff', 'pharmacist', 'pharmacy', 'pharmcy']) => 'pharmacy',
            $user->hasAnyRole(['cashier', 'casher', 'billing_staff', 'accountant']) => 'finance',
            $user->hasAnyRole(['radiology_staff', 'radiology_technician']) => 'radiology',
            $user->hasAnyRole(['lab_staff', 'laboratory_staff', 'lab_technician']) => 'laboratory',
            $user->hasRole('triage_nurse') => 'triage_nurse',
            $user->hasRole('nurse') => 'nurse',
            $user->hasAnyRole(['opd_staff', 'ipd_staff', 'receptionist']) => 'operations',
            $user->hasRole('patient') => 'patient',
            default => 'user',
        };
    }
}
