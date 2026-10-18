<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Support\TableFilters;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('name')
                    ->label('Name')
                    ->form([TextInput::make('name')->label('Name')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['name'] ?? null),
                        fn (Builder $query): Builder => $query->where('name', 'like', '%' . trim($data['name']) . '%'),
                    )),
                Filter::make('email')
                    ->label('Email')
                    ->form([TextInput::make('email')->label('Email address')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['email'] ?? null),
                        fn (Builder $query): Builder => $query->where('email', 'like', '%' . trim($data['email']) . '%'),
                    )),
                TableFilters::relationship('roles', 'name', 'Role'),
                TableFilters::dateRange('email_verified_at', 'Verified Date'),
                TableFilters::createdAt(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('reset_password')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset User Password')
                    ->modalDescription('This will send a password reset link to the user. Are you sure?')
                    ->modalSubmitActionLabel('Send Reset Link')
                    ->action(function ($record) {
                        $record->sendPasswordResetNotification(
                            \Illuminate\Support\Facades\Password::createToken($record)
                        );
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Password reset link sent')
                            ->body("A password reset link has been sent to {$record->email}")
                            ->success()
                            ->send();
                    }),
                Action::make('print')
                    ->label('Print')
                    ->url(fn ($record) => route('users.print', $record))
                    ->openUrlInNewTab()
                    ->color('success')
                    ->icon('heroicon-o-printer'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
