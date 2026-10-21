<?php

namespace App\Filament\Resources\FinanceStaff\Pages;

use App\Filament\Resources\FinanceStaff\FinanceStaffResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFinanceStaff extends ViewRecord
{
    protected static string $resource = FinanceStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
