<?php

namespace App\Filament\Resources\Prescriptions\Schemas;

use App\Models\Prescription;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrescriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Prescription Details')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('prescription_id')->label('Prescription ID'),
                        TextEntry::make('patient.full_name')->label('Patient')->placeholder('-'),
                        TextEntry::make('doctor.full_name')->label('Prescribing Doctor')->placeholder('-'),
                        TextEntry::make('encounter_type')->badge()->placeholder('-'),
                        TextEntry::make('encounter_id')->label('Encounter ID')->placeholder('-'),
                        TextEntry::make('prescribed_date')->label('Prescribed Date')->date(),
                        TextEntry::make('diagnosis')->label('Diagnosis / Chief Complaint')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('status')->badge()->color(fn (string $state): string => match ($state) {
                            'active' => 'success',
                            'dispensed' => 'info',
                            'expired' => 'warning',
                            'cancelled' => 'danger',
                            default => 'gray',
                        }),
                    ]),

                Section::make('Prescribed Medications (Rx)')
                    ->icon('heroicon-o-beaker')
                    ->schema([
                        TextEntry::make('medications_list')
                            ->hiddenLabel()
                            ->state(fn (Prescription $record): string => self::formatMedications($record))
                            ->html(),
                    ]),

                Section::make('Instructions & Notes')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        TextEntry::make('instructions')->label('General Instructions')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('notes')->label('Notes')->placeholder('-')->columnSpanFull(),
                    ]),

                Section::make('Audit Trail')
                    ->schema([
                        TextEntry::make('created_at')->dateTime()->placeholder('-'),
                        TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    /**
     * Render the prescription's medications as a compact HTML table so the
     * infolist looks like a real prescription pad.
     */
    private static function formatMedications(Prescription $record): string
    {
        $rows = $record->medicationRows();

        if ($rows === []) {
            return '<span class="text-gray-400">No medications recorded.</span>';
        }

        $cell = fn (?string $value): string => '<td style="border:1px solid #e5e7eb;padding:6px;">' . e($value ?? '—') . '</td>';
        $head = fn (string $label): string => '<th style="text-align:left;border:1px solid #e5e7eb;padding:6px;background:#f9fafb;">' . $label . '</th>';

        $html = '<div style="overflow-x:auto;"><table style="width:100%;border-collapse:collapse;"><thead><tr>'
            . $head('#') . $head('Drug / Medicine') . $head('Dosage') . $head('Frequency')
            . $head('Duration') . $head('Quantity') . $head('Instructions')
            . '</tr></thead><tbody>';

        foreach ($rows as $i => $row) {
            $html .= '<tr>'
                . $cell((string) ($i + 1))
                . '<td style="border:1px solid #e5e7eb;padding:6px;font-weight:600;">' . e($row['drug']) . '</td>'
                . $cell($row['dosage']) . $cell($row['frequency']) . $cell($row['duration'])
                . $cell($row['quantity']) . $cell($row['instructions'])
                . '</tr>';
        }

        return $html . '</tbody></table></div>';
    }
}
