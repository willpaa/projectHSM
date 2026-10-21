<?php

namespace App\Filament\Resources\FinanceStaff\Pages;

use App\Filament\Resources\FinanceStaff\FinanceStaffResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFinanceStaff extends EditRecord
{
    protected static string $resource = FinanceStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }
}
