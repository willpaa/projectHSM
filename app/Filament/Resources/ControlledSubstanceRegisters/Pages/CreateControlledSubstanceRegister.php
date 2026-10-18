<?php

namespace App\Filament\Resources\ControlledSubstanceRegisters\Pages;

use App\Filament\Resources\ControlledSubstanceRegisters\ControlledSubstanceRegisterResource;
use App\Models\Inventory;
use App\Services\ControlledSubstanceService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateControlledSubstanceRegister extends CreateRecord
{
    protected static string $resource = ControlledSubstanceRegisterResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }

    /**
     * Route creation through the controlled-substance service so the running
     * balance is computed with row locking and the pharmacist is stamped.
     */
    protected function handleRecordCreation(array $data): \App\Models\ControlledSubstanceRegister
    {
        $inventory = Inventory::query()->where('item_id', $data['inventory_item_id'])->firstOrFail();

        return ControlledSubstanceService::record(
            $inventory,
            (string) $data['transaction_type'],
            (int) $data['quantity'],
            Auth::id(),
            [
                'batch_no' => $data['batch_no'] ?? null,
                'patient_id' => $data['patient_id'] ?? null,
                'prescription_id' => $data['prescription_id'] ?? null,
                'witness' => $data['witness'] ?? null,
                'notes' => $data['notes'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now(),
            ],
        );
    }
}
