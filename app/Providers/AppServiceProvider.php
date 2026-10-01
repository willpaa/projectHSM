<?php

namespace App\Providers;

use App\Support\PatientAccess;
use App\Models\Appointments;
use App\Models\Beds;
use App\Models\Department;
use App\Models\Doctors;
use App\Models\Inventory;
use App\Models\Laboratory;
use App\Models\Nurse;
use App\Models\Patients;
use App\Models\Payments;
use App\Models\Pharmacy;
use App\Models\PharmacySale;
use App\Models\Prescription;
use App\Models\Radiology;
use App\Models\Reports;
use App\Models\ServiceRequest;
use App\Models\Staff;
use App\Models\Triage;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerAutomaticIdentifiers();

        DeleteAction::configureUsing(fn (DeleteAction $action) => $action->successNotificationTitle('Delete is successful.'));
        DeleteBulkAction::configureUsing(fn (DeleteBulkAction $action) => $action->successNotificationTitle('Delete is successful.'));

        if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            try {
                PatientAccess::ensureRolePermissions();
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    private function registerAutomaticIdentifiers(): void
    {
        $identifiers = [
            Patients::class => ['patient_id' => 'PAT', 'mrn' => 'MRN'],
            Appointments::class => ['appointment_id' => 'APT'],
            Department::class => ['department_id' => 'DEPT'],
            Doctors::class => ['doctor_id' => 'DOC'],
            Nurse::class => ['nurse_id' => 'NUR'],
            Staff::class => ['staff_id' => 'STF'],
            Beds::class => ['bed_id' => 'BED'],
            Inventory::class => ['item_id' => 'ITEM'],
            Laboratory::class => ['lab_id' => 'LAB'],
            Radiology::class => ['radiology_id' => 'RAD'],
            Triage::class => ['triage_id' => 'TRI'],
            Prescription::class => ['prescription_id' => 'RX'],
            Reports::class => ['report_id' => 'RPT'],
            Pharmacy::class => ['medicine_id' => 'MED'],
            PharmacySale::class => ['sale_id' => 'SALE'],
            ServiceRequest::class => ['request_number' => 'SR'],
            Payments::class => ['invoice_number' => 'INV'],
        ];

        foreach ($identifiers as $modelClass => $fields) {
            $modelClass::creating(function (Model $model) use ($fields): void {
                foreach ($fields as $field => $prefix) {
                    if (filled($model->getAttribute($field))) {
                        continue;
                    }

                    $model->setAttribute(
                        $field,
                        $prefix . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                    );
                }
            });
        }
    }
}
