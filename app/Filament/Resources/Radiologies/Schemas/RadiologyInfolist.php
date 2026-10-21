<?php

namespace App\Filament\Resources\Radiologies\Schemas;

use App\Support\ClinicalReportAccess;
use App\Support\ClinicalStaffAccess;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;

class RadiologyInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->components([
                Section::make('Radiology Details')
                    ->schema([
                        TextEntry::make('radiology_id'),
                        TextEntry::make('patient.patient_id')->label('Patient ID'),
                        TextEntry::make('patient.full_name')->label('Patient'),
                        TextEntry::make('doctor.full_name')->label('Doctor')->placeholder('-'),
                        TextEntry::make('radiologist.full_name')->label('Assigned Technician')->placeholder('Unassigned'),
                        TextEntry::make('exam_name')->placeholder('-'),
                        TextEntry::make('modality')->placeholder('-'),
                        TextEntry::make('body_part')->label('Body Part / Region')->placeholder('-'),
                        TextEntry::make('laterality')->label('Side')->formatStateUsing(fn (?string $state): string => match ($state) {
                            'right' => 'Right',
                            'left' => 'Left',
                            'bilateral' => 'Bilateral',
                            'not_applicable' => 'Not applicable',
                            default => '-',
                        }),
                        TextEntry::make('urgency')->badge()->placeholder('Routine'),
                        TextEntry::make('exam_date')->date()->placeholder('-'),
                        TextEntry::make('status')->badge(),
                    ])
                    ->columns(2),

                Section::make('Referral Information')
                    ->schema([
                        TextEntry::make('clinical_indication')->label('Clinical Indication')->prose()->placeholder('-')->columnSpanFull(),
                        TextEntry::make('clinical_history')->label('Relevant History / Examination')->prose()->placeholder('-')->columnSpanFull(),
                        TextEntry::make('clinical_question')->label('Clinical Question')->prose()->placeholder('-')->columnSpanFull(),
                        TextEntry::make('pregnancy_status')->label('Pregnancy Screening')->placeholder('-'),
                        TextEntry::make('contrast_plan')->label('Contrast Plan')->placeholder('-'),
                        TextEntry::make('contrast_safety_notes')->label('Contrast Safety Notes')->prose()->placeholder('-')->columnSpanFull(),
                        TextEntry::make('mri_safety_notes')->label('MRI Safety Notes')->prose()->placeholder('-')->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Radiology Findings')
                    ->description('Detailed clinical findings and conclusions')
                    ->headerActions([
                        Action::make('print')
                            ->label('Print Report')
                            ->icon('heroicon-o-printer')
                            ->extraAttributes(['onclick' => 'window.print(); return false;']),
                       
                    ])
                    ->schema([
                        TextEntry::make('result_status')->badge()->placeholder('-'),
                        TextEntry::make('completed_at')->label('Result Date')->dateTime()->placeholder('-'),
                        TextEntry::make('reviewed_at')->label('Doctor Review Date')->dateTime()->placeholder('Not reviewed'),
                        TextEntry::make('reviewingUser.name')->label('Reviewed By')->placeholder('-'),
                        TextEntry::make('doctor_review_notes')->label('Doctor Review Notes')->prose()->placeholder('-')->columnSpanFull(),
                        TextEntry::make('result_summary')
                            ->label('Result Summary')
                            ->placeholder('-')
                            ->prose()
                            ->columnSpanFull(),
                        TextEntry::make('report_technique')
                            ->label('Technique / Procedure')
                            ->prose()
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('comparison_studies')
                            ->label('Comparison with Prior Studies')
                            ->prose()
                            ->placeholder('No comparison recorded')
                            ->columnSpanFull(),
                        TextEntry::make('findings')
                            ->prose()
                            ->columnSpanFull(),
                        TextEntry::make('conclusion')
                            ->weight('bold')
                            ->columnSpanFull(),
                        TextEntry::make('critical_contact_name')
                            ->label('Critical Finding Communicated To')
                            ->placeholder('-'),
                        TextEntry::make('critical_contact_method')
                            ->label('Communication Method')
                            ->placeholder('-'),
                        TextEntry::make('critical_contacted_at')
                            ->label('Communication Time')
                            ->dateTime()
                            ->placeholder('-'),
                        ImageEntry::make('report_image')
                            ->label('Supporting Imaging Image')
                            ->size(400)
                            ->columnSpanFull(),
                        RepeatableEntry::make('reportRevisions')
                            ->label('Report Version History')
                            ->schema([
                                TextEntry::make('revision_number')->label('Version')->badge(),
                                TextEntry::make('revision_type')->label('Change Type')->badge(),
                                TextEntry::make('reason')->label('Reason')->placeholder('Initial report')->columnSpanFull(),
                                TextEntry::make('author.name')->label('Entered By')->placeholder('User account unavailable'),
                                TextEntry::make('created_at')->label('Saved At')->dateTime(),
                                TextEntry::make('report_data.findings')->label('Findings at this Version')->prose()->columnSpanFull(),
                                TextEntry::make('report_data.conclusion')->label('Conclusion at this Version')->prose()->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record): bool => $record->status === 'completed'
                        && (ClinicalReportAccess::canViewReport($record)
                            || ClinicalStaffAccess::canManageRadiology($record)
                            || (auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false))),
            ]);
    }
}
