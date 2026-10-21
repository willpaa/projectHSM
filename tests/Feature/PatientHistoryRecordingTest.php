<?php

use App\Models\Appointments;
use App\Models\PatientHistory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::create('patient_histories', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('patient_id');
        $table->string('encounter_id', 50)->nullable();
        $table->string('source', 50)->default('pharmacy');
        $table->string('activity', 120);
        $table->text('details')->nullable();
        $table->timestamp('occurred_at')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('appointments', function (Blueprint $table): void {
        $table->id();
        $table->string('appointment_id', 50);
        $table->unsignedBigInteger('patient_id');
        $table->unsignedBigInteger('doctor_id');
        $table->date('date');
        $table->time('time');
        $table->unsignedInteger('duration_minutes')->default(30);
        $table->string('visit_type', 20);
        $table->text('reason')->nullable();
        $table->string('token_no', 30)->nullable();
        $table->string('status', 30)->default('scheduled');
        $table->text('notes')->nullable();
        $table->string('created_by', 50)->nullable();
        $table->timestamps();
    });
});

test('appointment creation and status changes are added to patient history', function (): void {
    $appointment = Appointments::query()->create([
        'appointment_id' => 'APT-TEST-1',
        'patient_id' => 17,
        'doctor_id' => 4,
        'date' => '2026-10-13',
        'time' => '09:30:00',
        'visit_type' => 'OPD',
        'reason' => 'Follow-up',
    ]);

    $history = PatientHistory::query()->where('patient_id', 17)->firstOrFail();

    expect($history->source)->toBe('appointment')
        ->and($history->activity)->toBe('Appointment scheduled')
        ->and($history->encounter_id)->toBe('APT-TEST-1')
        ->and($history->details)->toContain('Reason: Follow-up');

    $appointment->update(['status' => 'completed']);

    expect(PatientHistory::query()->where('patient_id', 17)->count())->toBe(2)
        ->and(PatientHistory::query()->latest('id')->value('activity'))->toBe('Appointment updated');
});
