<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Resources\Inventories\InventoryResource;
use App\Services\ReorderService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_reorder_orders')
                ->label('Auto-Reorder Low Stock')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Generate reorder purchase orders')
                ->modalDescription('A draft purchase order will be created for each supplier that has items at or below their reorder level.')
                ->action(function (): void {
                    try {
                        $orders = ReorderService::generateDraftPurchaseOrders();

                        Notification::make()
                            ->title('Reorder orders generated.')
                            ->body("{$orders->count()} draft purchase order(s) created from low-stock items.")
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('No reorder orders were created.')
                            ->body($exception->getMessage())
                            ->warning()
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}

