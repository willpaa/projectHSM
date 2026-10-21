<?php

namespace App\Filament\Resources\FinanceStaff\Pages;

use App\Filament\Resources\FinanceStaff\FinanceStaffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinanceStaff extends ListRecords
{
    protected static string $resource = FinanceStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
