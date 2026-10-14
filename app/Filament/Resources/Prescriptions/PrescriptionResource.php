<?php

namespace App\Filament\Resources\Prescriptions;

use App\Filament\Resources\Prescriptions\Pages\CreatePrescription;
use App\Filament\Resources\Prescriptions\Pages\EditPrescription;
use App\Filament\Resources\Prescriptions\Pages\ListPrescriptions;
use App\Filament\Resources\Prescriptions\Pages\ViewPrescription;
use App\Filament\Resources\Prescriptions\Schemas\PrescriptionForm;
use App\Filament\Resources\Prescriptions\Schemas\PrescriptionInfolist;
use App\Filament\Resources\Prescriptions\Tables\PrescriptionsTable;
use App\Models\Prescription;
use App\Support\ClinicalReportAccess;
use App\Support\PatientPortal;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    //protected static ?string $navigationParentItem = 'Pharmacy Management';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PrescriptionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PrescriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrescriptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return PatientPortal::isPatientUser() ? 'My Prescriptions' : 'Prescriptions';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['patient', 'doctor']);

        if (PatientPortal::isPatientUser()) {
            $patientId = PatientPortal::currentPatientId();

            return $patientId ? $query->where('patient_id', $patientId) : $query->whereRaw('1 = 0');
        }

        // A logged-in doctor only sees the prescriptions they wrote.
        if (auth()->user()?->hasRole('doctor')
            && ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            $doctorId = ClinicalReportAccess::currentDoctorId();

            return $doctorId ? $query->where('doctor_id', $doctorId) : $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:Prescription');
    }

    public static function canCreate(): bool
    {
        return ! PatientPortal::isPatientUser();
    }

    public static function canEdit($record): bool
    {
        if (PatientPortal::isPatientUser()) {
            return false;
        }

        // A doctor may only edit the prescriptions they wrote.
        if (auth()->user()?->hasRole('doctor')
            && ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            return $record instanceof Prescription && ClinicalReportAccess::canViewReport($record);
        }

        return true;
    }

    public static function canDelete($record): bool
    {
        if (PatientPortal::isPatientUser()) {
            return false;
        }

        // A doctor may only delete the prescriptions they wrote.
        if (auth()->user()?->hasRole('doctor')
            && ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            return $record instanceof Prescription && ClinicalReportAccess::canViewReport($record);
        }

        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrescriptions::route('/'),
            'create' => CreatePrescription::route('/create'),
            'view' => ViewPrescription::route('/{record}'),
            'edit' => EditPrescription::route('/{record}/edit'),
        ];
    }
}
