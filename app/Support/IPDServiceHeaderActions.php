<?php

namespace App\Support;

use App\Models\Doctors;
use App\Models\IPD;
use App\Models\Laboratory;
use App\Models\Radiology;
use App\Models\Service;
use App\Models\Staff;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class IPDServiceHeaderActions
{
    private static function canUse(): bool
    {
        return ! PatientPortal::isPatientUser() && ClinicalReportAccess::isDoctorUser();
    }

    private static function encountersQuery(array $statuses): Builder
    {
        return IPD::query()
            ->with('patient')
            ->where('doctor_id', ClinicalReportAccess::currentDoctorId())
            ->whereIn('status', $statuses);
    }

    private static function encounterSelect(array $statuses): Select
    {
        return Select::make('ipd_id')
            ->label('IPD Patient / Encounter')
            ->placeholder('Search or choose an IPD patient')
            ->helperText('Your latest active IPD encounter is selected automatically. You can choose another assigned patient.')
            ->options(fn (): array => self::encountersQuery($statuses)->orderByDesc('created_at')->get()
                ->mapWithKeys(fn (IPD $ipd): array => [$ipd->id => trim(($ipd->patient?->full_name ?? 'Patient') . ' — ' . $ipd->encounter_id)])
                ->all())
            ->default(fn (): ?int => self::encountersQuery($statuses)->latest('created_at')->value('id'))
            ->searchable()->preload()->required();
    }

    public static function sendToLab(): Action
    {
        $statuses = ['admitted', 'transferred'];

        return Action::make('send_ipd_to_lab_header')
            ->label('Send to Lab')->icon('heroicon-o-beaker')->color('warning')
            ->visible(fn (): bool => self::canUse())
            ->form([
                self::encounterSelect($statuses),
                Select::make('service_id')->label('Lab Service / Type')
                    ->options(fn (): array => Service::query()->where('service_type', 'lab')->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(function ($state, callable $set): void { if ($service = ($state ? Service::find($state) : null)) { $set('test_name', $service->name); } })
                    ->required(),
                Textarea::make('test_name')->label('Test / Panel')->required()->rows(2),
                Select::make('test_type')->options(array_combine(['Hematology', 'Biochemistry', 'Microbiology', 'Serology', 'Immunology', 'Pathology', 'Endocrinology', 'General'], ['Hematology', 'Biochemistry', 'Microbiology', 'Serology', 'Immunology', 'Pathology', 'Endocrinology', 'General']))->default('General')->required(),
                Select::make('sample_type')->options(array_combine(['Blood', 'Urine', 'Stool', 'Sputum', 'Swab', 'Tissue', 'Other'], ['Blood', 'Urine', 'Stool', 'Sputum', 'Swab', 'Tissue', 'Other']))->default('Blood')->required(),
                Select::make('laboratorist_id')->label('Lab Technician')->options(fn (): array => Staff::query()->where('role', 'lab_technician')->where('status', 'active')->whereNotNull('user_id')->orderBy('full_name')->pluck('full_name', 'id')->all())->searchable()->required(),
            ])
            ->action(function (array $data): void {
                $ipd = self::encountersQuery(['admitted', 'transferred'])->findOrFail($data['ipd_id']);
                abort_unless(ClinicalReportAccess::canViewReport($ipd), 403);
                $exists = Laboratory::query()->where('patient_id', $ipd->patient_id)->where('doctor_id', $ipd->doctor_id)->where('encounter_id', $ipd->encounter_id)->whereIn('status', ['ordered', 'sample_collected', 'processing'])->exists();
                if ($exists) {
                    Notification::make()->title('Lab request already exists for this patient.')->warning()->send();
                    return;
                }
                $service = Service::query()->where('service_type', 'lab')->where('is_active', true)->findOrFail($data['service_id']);
                $technician = Staff::query()->with('user')->where('role', 'lab_technician')->where('status', 'active')->whereNotNull('user_id')->findOrFail($data['laboratorist_id']);
                $labId = 'LAB-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
                Laboratory::create(['lab_id' => $labId, 'patient_id' => $ipd->patient_id, 'doctor_id' => $ipd->doctor_id, 'laboratorist_id' => $technician->id, 'encounter_type' => 'IPD', 'encounter_id' => $ipd->encounter_id, 'test_name' => $data['test_name'], 'test_type' => $data['test_type'], 'sample_type' => $data['sample_type'], 'test_date' => now()->toDateString(), 'result_status' => 'pending', 'status' => 'ordered', 'cost' => $service->price]);
                if ($technician->user) {
                    Notification::make()->title('New Laboratory Queue')->body("IPD patient {$ipd->patient?->full_name} assigned to {$technician->full_name}.")->icon('heroicon-o-bell-alert')->sendToDatabase(collect([$technician->user]));
                }
                Notification::make()->title('Patient sent to Laboratory.')->body("Lab ID: {$labId}. The laboratorist will send the request to cashier after review.")->success()->send();
            });
    }

    public static function sendToRadiology(): Action
    {
        $statuses = ['admitted'];

        return Action::make('send_ipd_to_radiology_header')
            ->label('Send to Radiology')->icon('heroicon-o-photo')->color('gray')
            ->visible(fn (): bool => self::canUse())
            ->form([
                self::encounterSelect($statuses),
                Select::make('radiologist_id')->label('Radiology Technician')->options(fn (): array => Staff::query()->where('role', 'radiology_technician')->where('status', 'active')->whereNotNull('user_id')->orderBy('full_name')->pluck('full_name', 'id')->all())->searchable()->required(),
                Textarea::make('exam_name')->label('Exam')->default('IPD Requested Imaging')->required()->rows(2),
            ])
            ->action(function (array $data): void {
                $ipd = self::encountersQuery(['admitted'])->findOrFail($data['ipd_id']);
                abort_unless(ClinicalReportAccess::canViewReport($ipd), 403);
                $technician = Staff::query()->with('user')->where('role', 'radiology_technician')->where('status', 'active')->whereNotNull('user_id')->findOrFail($data['radiologist_id']);
                $radiologyId = 'RAD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
                Radiology::create(['radiology_id' => $radiologyId, 'patient_id' => $ipd->patient_id, 'doctor_id' => $ipd->doctor_id, 'radiologist_id' => $technician->id, 'encounter_type' => 'IPD', 'encounter_id' => $ipd->encounter_id, 'exam_name' => $data['exam_name'], 'modality' => 'X-Ray', 'exam_date' => now()->toDateString(), 'result_status' => 'pending', 'status' => 'ordered']);
                if ($technician->user) {
                    Notification::make()->title('New Radiology Queue')->body("IPD patient {$ipd->patient?->full_name} assigned to {$technician->full_name}.")->icon('heroicon-o-bell-alert')->sendToDatabase(collect([$technician->user]));
                }
                Notification::make()->title('Patient sent to Radiology.')->body("Radiology ID: {$radiologyId}. The radiologist will send the request to cashier after review.")->success()->send();
            });
    }
}
