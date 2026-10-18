<?php

namespace App\Support;

use App\Filament\Resources\Laboratories\LaboratoryResource;
use App\Filament\Resources\Radiologies\RadiologyResource;
use App\Models\Laboratory;
use App\Models\Radiology;
use App\Models\User;
use Filament\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;

class ClinicalResultNotifier
{
    public static function notifyDoctor(Laboratory|Radiology $record): bool
    {
        $doctor = $record->doctor()->with('user')->first();
        $doctorUser = $doctor?->user;

        if (! $doctorUser && filled($doctor?->email)) {
            $doctorUser = User::query()
                ->whereRaw('LOWER(TRIM(email)) = ?', [strtolower(trim((string) $doctor->email))])
                ->first();
        }

        if (! $doctorUser) {
            return false;
        }

        $resource = $record instanceof Laboratory
            ? LaboratoryResource::class
            : RadiologyResource::class;
        $resultType = $record instanceof Laboratory ? 'Laboratory' : 'Radiology';
        $recordNumber = $record instanceof Laboratory ? $record->lab_id : $record->radiology_id;

        Notification::make()
            ->title("{$resultType} result ready for review")
            ->body("Result for {$record->patient?->full_name} ({$recordNumber}) is ready.")
            ->icon($record instanceof Laboratory ? 'heroicon-o-beaker' : 'heroicon-o-document-magnifying-glass')
            ->actions([
                NotificationAction::make('view_result')
                    ->label('View result')
                    ->url($resource::getUrl('view', ['record' => $record])),
            ])
            ->info()
            ->sendToDatabase($doctorUser, isEventDispatched: true);

        return true;
    }
}
