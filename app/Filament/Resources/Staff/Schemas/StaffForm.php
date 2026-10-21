<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\User;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema, ?string $fixedRole = null): Schema
    {
        $roleField = $fixedRole === null
            ? Select::make('role')
                ->live()
                ->afterStateUpdated(fn (Set $set): mixed => $set('user_id', null))
                ->options([
                    'receptionist' => 'Receptionist',
                    'doctor' => 'Doctor',
                    'nurse' => 'Nurse',
                    'lab_technician' => 'Lab Technician',
                    'radiology_technician' => 'Radiology Technician',
                    'pharmacist' => 'Pharmacist',
                    'accountant' => 'Finance Staff',
                    'admin' => 'Admin',
                ])
                ->searchable()
                ->required()
            : Hidden::make('role')
                ->default($fixedRole)
                ->required();

        return $schema
            ->components([
                TextInput::make('staff_id')
                    ->required(),
                TextInput::make('full_name')
                    ->required(),
                Select::make('user_id')
                    ->label('Staff Login Account')
                    ->options(function (Get $get): array {
                        $roles = match ($get('role')) {
                            'lab_technician' => ['lab_technician', 'lab_staff', 'laboratory_staff'],
                            'radiology_technician' => ['radiology_technician', 'radiology_staff'],
                            'accountant' => ['accountant', 'cashier', 'casher', 'billing_staff'],
                            'pharmacist' => ['pharmacist', 'pharmacy_staff', 'pharmacy', 'pharmcy'],
                            default => null,
                        };

                        if (! $roles) {
                            return [];
                        }

                        return User::query()
                            ->whereHas('roles', fn ($query) => $query
                                ->whereIn('roles.name', $roles)
                                ->where('roles.guard_name', 'web'))
                            ->where(fn ($query) => $query
                                ->whereDoesntHave('staffProfile')
                                ->orWhere('id', $get('user_id')))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => in_array($get('role'), ['lab_technician', 'radiology_technician', 'accountant', 'pharmacist'], true))
                    ->visible(fn (Get $get): bool => in_array($get('role'), ['lab_technician', 'radiology_technician', 'accountant', 'pharmacist'], true))
                    ->helperText('Create a login account with the matching role first, then link it here.'),
                $roleField,
                TextInput::make('department')
                    ->default(null),
                Select::make('shift')
                    ->options(['day' => 'Day', 'night' => 'Night', 'rotational' => 'Rotational', 'custom' => 'Custom'])
                    ->default('day')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                DatePicker::make('join_date'),
                TextInput::make('salary')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on_leave' => 'On leave',
                        'terminated' => 'Terminated',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}
