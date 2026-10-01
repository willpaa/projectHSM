<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PharmacyBatches\PharmacyBatchResource;
use App\Models\PharmacyBatch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RolePharmacyExpiringStock extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Expiry Alerts')
            ->description('Expired lots and lots expiring within the next 30 days.')
            ->query(fn (): Builder => PharmacyBatch::query()
                ->with('inventoryItem')
                ->where('quantity_remaining', '>', 0)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', today()->copy()->addDays(30))
                ->orderBy('expiry_date')
                ->limit(8))
            ->columns([
                TextColumn::make('inventoryItem.item_name')->label('Medicine')->searchable(),
                TextColumn::make('batch_no')->label('Batch'),
                TextColumn::make('quantity_remaining')->label('Qty remaining')->numeric(),
                TextColumn::make('expiry_date')
                    ->label('Expiry')
                    ->date()
                    ->badge()
                    ->color(fn (PharmacyBatch $record): string => $record->expiry_date?->isBefore(today()) ? 'danger' : 'warning'),
                TextColumn::make('expiry_date')
                    ->label('Time left')
                    ->state(fn (PharmacyBatch $record): string => $record->expiry_date?->isBefore(today())
                        ? 'Expired'
                        : today()->diffInDays($record->expiry_date) . ' days'),
            ])
            ->recordUrl(fn (): string => PharmacyBatchResource::getUrl('index'))
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super_admin', 'pharmacy_staff', 'pharmacist', 'pharmacy', 'pharmcy']) ?? false;
    }
}
