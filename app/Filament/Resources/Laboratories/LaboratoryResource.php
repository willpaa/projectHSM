<?php

namespace App\Filament\Resources\Laboratories;

use App\Filament\Resources\Laboratories\Pages\CreateLaboratory;
use App\Filament\Resources\Laboratories\Pages\EditLaboratory;
use App\Filament\Resources\Laboratories\Pages\ListLaboratories;
use App\Filament\Resources\Laboratories\Pages\ViewLaboratory;
use App\Filament\Resources\Laboratories\Schemas\LaboratoryForm;
use App\Filament\Resources\Laboratories\Schemas\LaboratoryInfolist;
use App\Filament\Resources\Laboratories\Tables\LaboratoriesTable;
use App\Models\Laboratory;
use App\Support\ClinicalReportAccess;
use App\Support\ClinicalStaffAccess;
use App\Support\PatientPortal;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LaboratoryResource extends Resource
{
    protected static ?string $model = Laboratory::class;


    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return LaboratoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LaboratoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaboratoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return PatientPortal::isPatientUser() ? 'My Lab Requests' : 'Laboratories';
    }

    public static function canViewAny(): bool
    {
        if (PatientPortal::isPatientUser()) {
            return true;
        }

        if (ClinicalStaffAccess::isLabStaff()) {
            return ClinicalStaffAccess::canManageLaboratory();
        }

        return auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false;
    }

    public static function canView($record): bool
    {
        if (PatientPortal::isPatientUser()) {
            return (int) $record->patient_id === (int) PatientPortal::currentPatientId();
        }

        if (ClinicalReportAccess::isDoctorUser()) {
            return false;
        }

        if (ClinicalStaffAccess::isLabStaff()) {
            return ClinicalStaffAccess::canManageLaboratory($record);
        }

        if (ClinicalStaffAccess::isRadiologyStaff()) {
            return false;
        }

        return (bool) auth()->user()?->hasAnyRole(['admin', 'super_admin']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (PatientPortal::isPatientUser()) {
            return $query->where('patient_id', PatientPortal::currentPatientId());
        }

        if (ClinicalStaffAccess::isLabStaff()) {
            return $query->where('laboratorist_id', ClinicalStaffAccess::currentLaboratoristId() ?? 0);
        }

        if (PatientPortal::isPatientUser()) {
            return $query->where('patient_id', PatientPortal::currentPatientId());
        }

        if (auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLaboratories::route('/'),
            'create' => CreateLaboratory::route('/create'),
            'view' => ViewLaboratory::route('/{record}'),
            'edit' => EditLaboratory::route('/{record}/edit'),
        ];
    }
}
