<?php

namespace App\Support;

use App\Filament\Resources\IPDS\IPDResource;
use App\Models\Doctors;
use App\Models\IPD;
use App\Models\Laboratory;
use App\Models\OPD;
use App\Models\Radiology;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class OPDHeaderActions
{
    private static function encounterSelect(): Select
    {
        return Select::make('opd_id')
            ->label('OPD Patient / Encounter')
            ->placeholder('Search or choose an OPD patient')
            ->helperText('The latest open OPD encounter is selected automatically. Change it here if you are working with another patient.')
            ->options(fn (): array => self::openEncountersQuery()
                ->with('patient')
                ->orderByDesc('created_at')
                ->get()
                ->mapWithKeys(fn (OPD $opd): array => [$opd->id => trim(($opd->patient?->full_name ?? 'Patient') . ' — ' . $opd->encounter_id)])
                ->all())
            ->default(fn (): ?int => self::defaultOpenEncounterId())
            ->searchable()
            ->preload()
            ->required();
    }

    private static function defaultOpenEncounterId(): ?int
    {
        return self::openEncountersQuery()
            ->orderByDesc('created_at')
            ->value('id');
    }

    private static function openEncountersQuery(): Builder
    {
        $query = OPD::query()->where('status', 'open');
        $user = auth()->user();

        if ($user?->hasAnyRole(['admin', 'super_admin', 'opd_staff'])) {
            return $query;
        }

        $doctorId = ClinicalReportAccess::currentDoctorId();

        return $doctorId
            ? $query->where('doctor_id', $doctorId)
            : $query->whereRaw('1 = 0');
    }

    private static function findOpenEncounter(array $data): OPD
    {
        $record = OPD::query()->where('status', 'open')->findOrFail($data['opd_id']);
        abort_unless(self::canAccess($record), 403);

        return $record;
    }

    private static function canAccess(OPD $record): bool
    {
        $user = auth()->user();

        return $user
            && ! PatientPortal::isPatientUser()
            && ($user->hasAnyRole(['admin', 'super_admin', 'opd_staff']) || ClinicalReportAccess::canViewReport($record));
    }

    public static function sendToIpd(): Action
    {
        return Action::make('send_opd_to_ipd')
            ->label('Send to IPD')->icon('heroicon-o-arrow-right-circle')->color('warning')
            ->visible(fn (): bool => ! PatientPortal::isPatientUser())
            ->form([self::encounterSelect(), Select::make('doctor_id')->label('Doctor')->options(fn (): array => Doctors::query()->where('status', 'active')->orderBy('full_name')->pluck('full_name', 'id')->all())->searchable()->required()])
            ->action(function (array $data): void {
                $record = self::findOpenEncounter($data);
                if (IPD::query()->where('patient_id', $record->patient_id)->where('status', 'admitted')->exists()) {
                    Notification::make()->title('Patient already has an open IPD encounter.')->warning()->send();
                    return;
                }

                $encounterId = 'IPD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
                IPD::create(['encounter_id' => $encounterId, 'patient_id' => $record->patient_id, 'type' => 'IPD', 'doctor_id' => $data['doctor_id'], 'status' => 'admitted']);
                $record->update(['status' => 'transferred', 'encounter_id' => $encounterId]);

                $ipdUsers = User::usersByRoleName('ipd_staff');
                if ($ipdUsers->isNotEmpty()) {
                    Notification::make()->title('New IPD Queue')->body("Patient {$record->patient?->full_name} sent to IPD.")->icon('heroicon-o-bell-alert')->sendToDatabase($ipdUsers);
                }
                $assignedDoctor = Doctors::query()->with('user')->find($data['doctor_id']);
                if ($assignedDoctor?->user) {
                    Notification::make()->title('New IPD Patient Assigned')->body("Patient {$record->patient?->full_name} has been admitted and assigned to you in IPD.")->icon('heroicon-o-user-plus')->actions([Action::make('view')->label('View Patient')->url(IPDResource::getUrl('index'))])->sendToDatabase($assignedDoctor->user);
                }
                Notification::make()->title('Patient sent to IPD.')->success()->send();
            });
    }

    public static function callPatient(): Action
    {
        return Action::make('call_opd_patient')
            ->label('Call')->icon('heroicon-o-phone')->color('success')
            ->visible(fn (): bool => ! PatientPortal::isPatientUser())
            ->form([self::encounterSelect()])
            ->action(function (array $data): void {
                $phone = self::findOpenEncounter($data)->patient?->phone;
                if (blank($phone)) {
                    Notification::make()->title('This patient has no phone number.')->warning()->send();
                    return;
                }
                Notification::make()
                    ->title('Call ' . ($phone ?? ''))
                    ->actions([Action::make('start_call')->label('Call patient')->url('tel:' . preg_replace('/[^0-9+]/', '', $phone))->button()])
                    ->send();
            });
    }

    public static function sendToLab(): Action
    {
        return Action::make('send_opd_to_lab')
            ->label('Send to Lab')->icon('heroicon-o-beaker')->color('warning')
            ->visible(fn (): bool => ! PatientPortal::isPatientUser())
            ->form([
                self::encounterSelect(),
                Select::make('service_id')->label('Lab Service / Type')->options(fn (): array => Service::query()->where('service_type', 'lab')->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload()->live()->afterStateUpdated(function ($state, callable $set): void { if ($service = ($state ? Service::find($state) : null)) { $set('test_name', $service->name); } })->required(),
                TextInput::make('test_name')->label('Test / Panel')->required()->maxLength(255),
                Select::make('test_type')->label('Test Category')->options(array_combine(['Hematology', 'Biochemistry', 'Microbiology', 'Serology', 'Immunology', 'Pathology', 'Endocrinology', 'General'], ['Hematology', 'Biochemistry', 'Microbiology', 'Serology', 'Immunology', 'Pathology', 'Endocrinology', 'General']))->default('General')->required(),
                Select::make('sample_type')->label('Sample Type')->options(array_combine(['Blood', 'Urine', 'Stool', 'Sputum', 'Swab', 'Tissue', 'Other'], ['Blood', 'Urine', 'Stool', 'Sputum', 'Swab', 'Tissue', 'Other']))->default('Blood')->required(),
                Select::make('laboratorist_id')->label('Lab Technician')->options(fn (): array => Staff::query()->where('role', 'lab_technician')->where('status', 'active')->whereNotNull('user_id')->orderBy('full_name')->pluck('full_name', 'id')->all())->searchable()->required(),
            ])
            ->action(function (array $data): void {
                $record = self::findOpenEncounter($data);
                if (Laboratory::query()->where('patient_id', $record->patient_id)->where('doctor_id', $record->doctor_id)->where('encounter_id', $record->encounter_id)->whereIn('status', ['ordered', 'sample_collected', 'processing'])->exists()) {
                    Notification::make()->title('Lab request already exists for this patient.')->warning()->send();
                    return;
                }
                $labId = 'LAB-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
                $service = Service::find($data['service_id']);
                $technician = Staff::query()->with('user')->where('role', 'lab_technician')->where('status', 'active')->findOrFail($data['laboratorist_id']);
                Laboratory::create(['lab_id' => $labId, 'patient_id' => $record->patient_id, 'doctor_id' => $record->doctor_id, 'laboratorist_id' => $technician->id, 'encounter_type' => 'OPD', 'encounter_id' => $record->encounter_id, 'test_name' => $data['test_name'], 'test_type' => $data['test_type'], 'sample_type' => $data['sample_type'], 'test_date' => now()->toDateString(), 'result_status' => 'pending', 'status' => 'ordered', 'cost' => $service?->price ?? 0]);
                if ($technician->user) {
                    Notification::make()->title('New Laboratory Queue')->body("OPD patient {$record->patient?->full_name} assigned to {$technician->full_name}.")->icon('heroicon-o-bell-alert')->sendToDatabase(collect([$technician->user]));
                }
                Notification::make()->title('Patient sent to Laboratory.')->body("Lab ID: {$labId}. The laboratorist will send the request to cashier after review.")->success()->send();
            });
    }

    public static function sendToRadiology(): Action
    {
        return Action::make('send_opd_to_radiology')
            ->label('Send to Radiology')->icon('heroicon-o-photo')->color('gray')
            ->visible(fn (): bool => ! PatientPortal::isPatientUser())
            ->modalHeading('Radiology Referral')
            ->form([
                self::encounterSelect(),
                Select::make('radiologist_id')->label('Radiology Technician')->options(fn (): array => Staff::query()->where('role', 'radiology_technician')->where('status', 'active')->whereNotNull('user_id')->orderBy('full_name')->pluck('full_name', 'id')->all())->searchable()->required(),
                Select::make('modality')->options(['X-Ray' => 'X-Ray', 'Ultrasound' => 'Ultrasound', 'CT' => 'CT', 'MRI' => 'MRI', 'Fluoroscopy' => 'Fluoroscopy', 'Mammography' => 'Mammography', 'Nuclear Medicine' => 'Nuclear Medicine'])->required(),
                TextInput::make('exam_name')->label('Examination')->required()->maxLength(120),
                TextInput::make('body_part')->label('Body Part / Region')->required()->maxLength(120),
                Select::make('laterality')->label('Side')->options(['not_applicable' => 'Not applicable', 'right' => 'Right', 'left' => 'Left', 'bilateral' => 'Bilateral']),
                Select::make('urgency')->options(['routine' => 'Routine', 'urgent' => 'Urgent', 'emergency' => 'Emergency'])->default('routine')->required(),
                Textarea::make('clinical_indication')->label('Clinical Indication')->required()->rows(3),
                Textarea::make('clinical_history')->label('Relevant History / Examination Findings')->rows(3),
                Textarea::make('clinical_question')->label('Clinical Question')->required()->rows(2),
                Select::make('pregnancy_status')->label('Pregnancy Screening (if applicable)')->options(['not_applicable' => 'Not applicable', 'screened_not_pregnant' => 'Screened: not pregnant', 'possible_or_confirmed' => 'Possible or confirmed', 'not_screened' => 'Not yet screened / unknown'])->default('not_screened'),
                Select::make('contrast_plan')->label('Contrast Plan')->options(['not_requested' => 'No contrast requested', 'requested' => 'Contrast requested; radiology to vet', 'radiologist_to_decide' => 'Radiologist to decide'])->default('radiologist_to_decide'),
                Textarea::make('contrast_safety_notes')->label('Contrast Safety Notes')->rows(2),
                Textarea::make('mri_safety_notes')->label('MRI Safety Notes')->rows(2),
            ])
            ->action(function (array $data): void {
                $record = self::findOpenEncounter($data);
                $technician = Staff::query()->with('user')->where('role', 'radiology_technician')->where('status', 'active')->whereNotNull('user_id')->findOrFail($data['radiologist_id']);
                $radiologyId = 'RAD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
                Radiology::create(['radiology_id' => $radiologyId, 'patient_id' => $record->patient_id, 'doctor_id' => $record->doctor_id, 'radiologist_id' => $technician->id, 'encounter_type' => 'OPD', 'encounter_id' => $record->encounter_id, 'exam_name' => $data['exam_name'], 'modality' => $data['modality'], 'body_part' => $data['body_part'], 'laterality' => $data['laterality'] ?? null, 'clinical_indication' => $data['clinical_indication'], 'clinical_history' => $data['clinical_history'] ?? null, 'clinical_question' => $data['clinical_question'], 'urgency' => $data['urgency'], 'pregnancy_status' => $data['pregnancy_status'] ?? null, 'contrast_plan' => $data['contrast_plan'] ?? null, 'contrast_safety_notes' => $data['contrast_safety_notes'] ?? null, 'mri_safety_notes' => $data['mri_safety_notes'] ?? null, 'exam_date' => null, 'result_status' => 'pending', 'status' => 'ordered']);
                if ($technician->user) {
                    Notification::make()->title('New Radiology Queue')->body("OPD patient {$record->patient?->full_name} assigned to {$technician->full_name}.")->icon('heroicon-o-bell-alert')->sendToDatabase(collect([$technician->user]));
                }
                Notification::make()->title('Patient sent to Radiology.')->body("Radiology ID: {$radiologyId}. The radiologist will send the request to cashier after review.")->success()->send();
            });
    }
}
