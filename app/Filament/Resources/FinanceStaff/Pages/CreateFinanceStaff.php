<?php

namespace App\Filament\Resources\FinanceStaff\Pages;

use App\Filament\Resources\FinanceStaff\FinanceStaffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinanceStaff extends CreateRecord
{
    protected static string $resource = FinanceStaffResource::class;

    protected static bool $canCreateAnother = false;
}
