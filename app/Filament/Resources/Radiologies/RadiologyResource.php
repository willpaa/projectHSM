<?php

namespace App\Filament\Resources\Radiologies;

use App\Filament\Resources\Radiologies\Pages\CreateRadiology;
use App\Filament\Resources\Radiologies\Pages\EditRadiology;
use App\Filament\Resources\Radiologies\Pages\ListRadiologies;
use App\Filament\Resources\Radiologies\Pages\ViewRadiology;
use App\Filament\Resources\Radiologies\Schemas\RadiologyForm;
use App\Filament\Resources\Radiologies\Schemas\RadiologyInfolist;
use App\Filament\Resources\Radiologies\Tables\RadiologiesTable;
use App\Models\Radiology;
use App\Support\ClinicalReportAccess;
use App\Support\ClinicalStaffAccess;
use App\Support\PatientPortal;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RadiologyResource extends Resource
{


    protected static ?string $model = Radiology::class;



    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

   

    protected static ?string $recordTitleAttribute = 'radiology_id';

    public static function form(Schema $schema): Schema
    {
        return RadiologyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RadiologyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RadiologiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return PatientPortal::isPatientUser() ? 'My Radiology Requests' : 'Radiologies';
    }

    public static function canViewAny(): bool
    {
        if (ClinicalStaffAccess::isRadiologyStaff()) {
            return ClinicalStaffAccess::canManageRadiology();
        }

        if (ClinicalStaffAccess::isLabStaff()) {
            return false;
        }

        return parent::canViewAny();
    }

    public static function canView($record): bool
    {
        if (PatientPortal::isPatientUser()) {
            return (int) $record->patient_id === (int) PatientPortal::currentPatientId();
        }

        if (ClinicalReportAccess::isDoctorUser()) {
            return ClinicalReportAccess::canViewReport($record);
        }

        if (ClinicalStaffAccess::isRadiologyStaff()) {
            return ClinicalStaffAccess::canManageRadiology($record);
        }

        if (ClinicalStaffAccess::isLabStaff()) {
            return false;
        }

        return (bool) auth()->user()?->can('View:Radiology');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (PatientPortal::isPatientUser()) {
            return $query->where('patient_id', PatientPortal::currentPatientId());
        }

        if (ClinicalReportAccess::isDoctorUser()) {
            return $query->where('doctor_id', ClinicalReportAccess::currentDoctorId());
        }

        if (ClinicalStaffAccess::isRadiologyStaff()) {
            return $query->where('radiologist_id', ClinicalStaffAccess::currentRadiologistId() ?? 0);
        }

        if (ClinicalStaffAccess::isLabStaff()) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return ! PatientPortal::isPatientUser() && ! ClinicalStaffAccess::isClinicalStaff();
    }

    public static function canEdit($record): bool
    {
        return ! PatientPortal::isPatientUser() && ! ClinicalStaffAccess::isClinicalStaff();
    }

    public static function canDelete($record): bool
    {
        return ! PatientPortal::isPatientUser() && ! ClinicalStaffAccess::isClinicalStaff();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRadiologies::route('/'),
            'create' => CreateRadiology::route('/create'),
            'view' => ViewRadiology::route('/{record}'),
            'edit' => EditRadiology::route('/{record}/edit'),
        ];
    }
}
