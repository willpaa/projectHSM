<?php

namespace App\Filament\Resources\Triages;

use App\Filament\Resources\Triages\Pages\CreateTriage;
use App\Filament\Resources\Triages\Pages\EditTriage;
use App\Filament\Resources\Triages\Pages\ListTriages;
use App\Filament\Resources\Triages\Pages\ViewTriage;
use App\Filament\Resources\Triages\Schemas\TriageForm;
use App\Filament\Resources\Triages\Schemas\TriageInfolist;
use App\Filament\Resources\Triages\Tables\TriagesTable;
use App\Models\Triage;
use App\Support\PatientPortal;
use App\Support\ClinicalNurseAccess;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TriageResource extends Resource
{
    protected static ?string $model = Triage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $recordTitleAttribute = 'triage_id';

    public static function form(Schema $schema): Schema
    {
        return TriageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TriageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TriagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return PatientPortal::isPatientUser() ? 'My Triage' : 'Triages';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['patient', 'nurse']);

        if (PatientPortal::isPatientUser()) {
            return $query->where('patient_id', PatientPortal::currentPatientId());
        }

        if (ClinicalNurseAccess::isTriageNurse()
            && ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            $nurseId = ClinicalNurseAccess::currentNurseId('triage_nurse');

            return $query
                ->where(function (Builder $triageQuery) use ($nurseId): void {
                    $triageQuery->where('nurse_id', $nurseId ?? 0)
                        ->orWhere(function (Builder $unassignedQuery): void {
                            $unassignedQuery
                                ->whereNull('nurse_id')
                                ->whereIn('status', ['waiting', 'in_progress']);
                        });
                })
                ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
                ->orderBy('created_at');
        }

        return $query
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->orderBy('created_at');
    }

    public static function canCreate(): bool
    {
        return ! PatientPortal::isPatientUser()
            && (auth()->user()?->hasAnyRole(['admin', 'super_admin'])
                || (ClinicalNurseAccess::isTriageNurse()
                    && ClinicalNurseAccess::currentNurseId('triage_nurse') !== null));
    }

    public static function canEdit($record): bool
    {
        return ! PatientPortal::isPatientUser()
            && $record instanceof Triage
            && ClinicalNurseAccess::canAccessTriage($record)
            && $record->status !== 'sent_to_opd';
    }

    public static function canDelete($record): bool
    {
        return ! PatientPortal::isPatientUser()
            && auth()->user()?->hasAnyRole(['admin', 'super_admin']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTriages::route('/'),
            'create' => CreateTriage::route('/create'),
            'view' => ViewTriage::route('/{record}'),
            'edit' => EditTriage::route('/{record}/edit'),
        ];
    }
}
