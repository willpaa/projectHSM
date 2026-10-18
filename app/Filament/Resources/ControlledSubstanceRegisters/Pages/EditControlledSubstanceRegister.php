<?php

namespace App\Filament\Resources\ControlledSubstanceRegisters\Pages;

use App\Filament\Resources\ControlledSubstanceRegisters\ControlledSubstanceRegisterResource;
use Filament\Resources\Pages\EditRecord;

/**
 * Register entries are an immutable audit trail, so editing is disabled.
 * This page exists only to satisfy the resource page contract.
 */
class EditControlledSubstanceRegister extends EditRecord
{
    protected static string $resource = ControlledSubstanceRegisterResource::class;

    protected static bool $canEdit = false;

    public function mount($record): void
    {
        parent::mount($record);

        $this->redirect(ControlledSubstanceRegisterResource::getUrl('view', ['record' => $record]));
    }
}
