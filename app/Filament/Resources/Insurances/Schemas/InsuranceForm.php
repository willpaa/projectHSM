<?php

namespace App\Filament\Resources\Insurances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InsuranceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('policy_id')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                Select::make('patient_id')
                    ->relationship('patient', 'full_name')
                    ->required(),
                TextInput::make('provider_name')
                    ->required()
                    ->maxLength(120),
                TextInput::make('policy_no')
                    ->required()
                    ->maxLength(80)
                    ->unique(ignoreRecord: true),
                TextInput::make('coverage_limit')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0.0),
                TextInput::make('co_pay')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0.0),
                DatePicker::make('valid_from'),
                DatePicker::make('valid_to')
                    ->minDate(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('valid_from')),
                TextInput::make('claim_id')
                    ->default(null),
                Select::make('claim_status')
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'settled' => 'Settled',
                    ])
                    ->default('submitted')
                    ->required(),
                TextInput::make('approved_amount')
                    ->numeric()
                    ->minValue(0)
                    ->default(null),
            ]);
    }
}
