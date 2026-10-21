<?php

namespace App\Filament\Resources\Nurses\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class NurseForm
{
    public static function configure(Schema $schema, ?string $fixedRole = null): Schema
    {
        $roleField = $fixedRole === null
            ? Select::make('role')
                ->options(['nurse' => 'Nurse', 'triage_nurse' => 'Triage Nurse'])
                ->live()
                ->afterStateUpdated(fn (Set $set): mixed => $set('user_id', null))
                ->required()
            : Hidden::make('role')->default($fixedRole)->required();

        return $schema
            ->components([
                TextInput::make('nurse_id')
                    ->required(),
                TextInput::make('full_name')
                    ->required(),
                $roleField,
                Select::make('user_id')
                    ->label('Login Account')
                    ->options(function (Get $get): array {
                        $role = $get('role');

                        if (! in_array($role, ['nurse', 'triage_nurse'], true)) {
                            return [];
                        }

                        return User::query()
                            ->whereHas('roles', fn ($query) => $query
                                ->where('roles.name', $role)
                                ->where('roles.guard_name', 'web'))
                            ->where(fn ($query) => $query
                                ->whereDoesntHave('nurseProfile')
                                ->orWhere('id', $get('user_id')))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => in_array($get('role'), ['nurse', 'triage_nurse'], true))
                    ->helperText('The selected account must already have the matching Nurse or Triage Nurse role.'),
                Select::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->default(null),
                TextInput::make('license_no')
                    ->default(null),
                Select::make('shift')
                    ->options(['day' => 'Day', 'night' => 'Night', 'rotational' => 'Rotational', 'custom' => 'Custom'])
                    ->default('day')
                    ->required(),
                DatePicker::make('join_date'),
                Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On leave'])
                    ->default('active')
                    ->required(),
                TextInput::make('emergency_contact')
                    ->default(null),
            ]);
    }
}
