<?php

namespace App\Filament\Resources\Appointments;

use App\Filament\Resources\Appointments\Pages\CreateAppointments;
use App\Filament\Resources\Appointments\Pages\EditAppointments;
use App\Filament\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Resources\Appointments\Pages\ViewAppointments;
use App\Filament\Resources\Appointments\Schemas\AppointmentsForm;
use App\Filament\Resources\Appointments\Tables\AppointmentsTable;
use App\Models\Appointments;
use App\Support\ClinicalReportAccess;
use App\Support\PatientPortal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentsResource extends Resource
{
    protected static ?string $model = Appointments::class;

    protected static ?string $recordTitleAttribute = 'appointment_id';

    protected static ?string $navigationLabel = 'Appointments';

    protected static string|\UnitEnum|null $navigationGroup = 'Clinical Operations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AppointmentsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['patient', 'doctor']);

        if (PatientPortal::isPatientUser()) {
            $patientId = PatientPortal::currentPatientId();

            return $patientId ? $query->where('patient_id', $patientId) : $query->whereRaw('1 = 0');
        }

        if (auth()->user()?->hasRole('doctor')
            && ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            $doctorId = ClinicalReportAccess::currentDoctorId();

            return $doctorId ? $query->where('doctor_id', $doctorId) : $query->whereRaw('1 = 0');
        }

        return auth()->user()?->hasAnyRole(['admin', 'super_admin', 'receptionist', 'opd_staff'])
            ? $query
            : $query->whereRaw('1 = 0');
    }

    public static function canViewAny(): bool
    {
        if (PatientPortal::isPatientUser()) {
            return PatientPortal::currentPatientId() !== null;
        }

        if (auth()->user()?->hasRole('doctor')
            && ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            return ClinicalReportAccess::currentDoctorId() !== null;
        }

        return auth()->user()?->hasAnyRole(['admin', 'super_admin', 'receptionist', 'opd_staff']) ?? false;
    }

    public static function canView($record): bool
    {
        if (PatientPortal::isPatientUser()) {
            return (int) $record->patient_id === (int) PatientPortal::currentPatientId();
        }

        if (auth()->user()?->hasRole('doctor')
            && ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            return ClinicalReportAccess::canViewReport($record);
        }

        return auth()->user()?->hasAnyRole(['admin', 'super_admin', 'receptionist', 'opd_staff']) ?? false;
    }

    public static function canCreate(): bool
    {
        if (PatientPortal::isPatientUser()) {
            return PatientPortal::currentPatientId() !== null;
        }

        if (auth()->user()?->hasRole('doctor')
            && ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            return ClinicalReportAccess::currentDoctorId() !== null;
        }

        return auth()->user()?->hasAnyRole(['admin', 'super_admin', 'receptionist', 'opd_staff']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return in_array($record->status, ['requested', 'scheduled'], true)
            && (auth()->user()?->hasAnyRole(['admin', 'super_admin', 'receptionist', 'opd_staff']) ?? false);
    }

    public static function canTransition(Appointments $appointment, string $nextStatus): bool
    {
        if (PatientPortal::isPatientUser()) {
            return $nextStatus === 'cancelled'
                && (int) $appointment->patient_id === (int) PatientPortal::currentPatientId();
        }

        $user = auth()->user();

        if ($user?->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        if ($user?->hasRole('doctor')) {
            return ClinicalReportAccess::canViewReport($appointment)
                && in_array($nextStatus, ['checked_in', 'in_progress', 'completed'], true);
        }

        return ($user?->hasAnyRole(['receptionist', 'opd_staff']) ?? false)
            && in_array($nextStatus, ['scheduled', 'checked_in', 'no_show', 'cancelled'], true);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointments::route('/'),
            'create' => CreateAppointments::route('/create'),
            'view' => ViewAppointments::route('/{record}'),
            'edit' => EditAppointments::route('/{record}/edit'),
        ];
    }
}
