<?php

namespace App\Filament\Resources\Appointments;

use App\Filament\Resources\Appointments\Pages\CreateAppointments;
use App\Filament\Resources\Appointments\Pages\EditAppointments;
use App\Filament\Resources\Appointments\Pages\ListAppointments;
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

        return $query;
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
            'edit' => EditAppointments::route('/{record}/edit'),
        ];
    }
}
