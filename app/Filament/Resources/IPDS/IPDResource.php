<?php

namespace App\Filament\Resources\IPDS;

use App\Filament\Resources\IPDS\Pages\CreateIPD;
use App\Filament\Resources\IPDS\Pages\EditIPD;
use App\Filament\Resources\IPDS\Pages\ListIPDS;
use App\Filament\Resources\IPDS\Pages\ViewIPD;
use App\Filament\Resources\IPDS\Schemas\IPDForm;
use App\Filament\Resources\IPDS\Schemas\IPDInfolist;
use App\Filament\Resources\IPDS\Tables\IPDSTable;
use App\Models\IPD;
use App\Support\ClinicalReportAccess;
use App\Support\PatientPortal;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IPDResource extends Resource
{
    protected static ?string $model = IPD::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return IPDForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IPDInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IPDSTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return PatientPortal::isPatientUser() ? 'My IPD Visits' : 'IPDS';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['patient', 'doctor']);

        if (PatientPortal::isPatientUser()) {
            $patientId = PatientPortal::currentPatientId();

            return $patientId ? $query->where('patient_id', $patientId) : $query->whereRaw('1 = 0');
        }

        // A logged-in doctor only sees the IPD encounters assigned to them.
        if (auth()->user()?->hasRole('doctor')
            && ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            $doctorId = ClinicalReportAccess::currentDoctorId();

            return $doctorId ? $query->where('doctor_id', $doctorId) : $query->whereRaw('1 = 0');
        }

        return $query;
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

        // A doctor may only edit the IPD encounters assigned to them.
        if (auth()->user()?->hasRole('doctor')
            && ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            return $record instanceof IPD && ClinicalReportAccess::canViewReport($record);
        }

        return true;
    }

    public static function canDelete(mixed $record): bool
    {
        return $record instanceof IPD
            && ! PatientPortal::isPatientUser()
            && ClinicalReportAccess::canViewReport($record);
    }

    public static function canDeleteAny(): bool
    {
        return ! PatientPortal::isPatientUser()
            && ClinicalReportAccess::isDoctorUser();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIPDS::route('/'),
            'create' => CreateIPD::route('/create'),
            'view' => ViewIPD::route('/{record}'),
            'edit' => EditIPD::route('/{record}/edit'),
        ];
    }
}
