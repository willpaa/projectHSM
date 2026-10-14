<?php

use App\Filament\Resources\IPDS\IPDResource;
use App\Filament\Resources\OPDS\OPDResource;
use App\Models\IPD;
use App\Models\OPD;
use App\Models\User;
use App\Support\ClinicalReportAccess;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Ensures a logged-in doctor's OPD and IPD resources are scoped so that only the
 * encounters assigned to that doctor (via doctor_id) are visible and editable.
 * This is what connects OPD/IPD to the doctor account.
 */
beforeEach(function (): void {
    Schema::create('doctors', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('email')->nullable();
    });

    DB::table('doctors')->insert([
        ['id' => 12, 'user_id' => 44, 'email' => 'assigned@example.test'],
        ['id' => 15, 'user_id' => 45, 'email' => 'other@example.test'],
    ]);

    // Two OPD encounters: one assigned to the logged-in doctor, one to another.
    Schema::create('o_p_d_s', function (Blueprint $table): void {
        $table->id();
        $table->string('encounter_id', 50)->unique();
        $table->unsignedBigInteger('patient_id');
        $table->unsignedBigInteger('doctor_id');
        $table->string('status', 20)->default('open');
        $table->timestamps();
    });

    DB::table('o_p_d_s')->insert([
        ['encounter_id' => 'OPD-MINE', 'patient_id' => 1, 'doctor_id' => 12, 'status' => 'open', 'created_at' => now(), 'updated_at' => now()],
        ['encounter_id' => 'OPD-OTHER', 'patient_id' => 2, 'doctor_id' => 15, 'status' => 'open', 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Two IPD encounters: one assigned to the logged-in doctor, one to another.
    Schema::create('i_p_d_s', function (Blueprint $table): void {
        $table->id();
        $table->string('encounter_id', 50)->unique();
        $table->unsignedBigInteger('patient_id');
        $table->unsignedBigInteger('doctor_id');
        $table->string('status', 20)->default('admitted');
        $table->timestamps();
    });

    DB::table('i_p_d_s')->insert([
        ['encounter_id' => 'IPD-MINE', 'patient_id' => 1, 'doctor_id' => 12, 'status' => 'admitted', 'created_at' => now(), 'updated_at' => now()],
        ['encounter_id' => 'IPD-OTHER', 'patient_id' => 2, 'doctor_id' => 15, 'status' => 'admitted', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $user = Mockery::mock(User::class)->makePartial();
    $user->id = 44;
    $user->email = 'assigned@example.test';
    $user->shouldReceive('hasRole')
        ->withArgs(fn (string $role): bool => in_array($role, ['doctor', 'patient'], true))
        ->andReturnUsing(fn (string $role): bool => $role === 'doctor');
    $user->shouldReceive('hasAnyRole')
        ->andReturnFalse();

    Auth::guard()->setUser($user);
});

test('a doctor only sees the OPD encounters assigned to them', function (): void {
    expect(ClinicalReportAccess::currentDoctorId())->toBe(12);

    $encounterIds = OPDResource::getEloquentQuery()->pluck('encounter_id')->all();

    expect($encounterIds)->toBe(['OPD-MINE'])
        ->and(OPDResource::canEdit(new OPD(['doctor_id' => 12])))->toBeTrue()
        ->and(OPDResource::canEdit(new OPD(['doctor_id' => 15])))->toBeFalse()
        ->and(OPDResource::canDelete(new OPD(['doctor_id' => 12])))->toBeTrue()
        ->and(OPDResource::canDelete(new OPD(['doctor_id' => 15])))->toBeFalse();
});

test('a doctor only sees the IPD encounters assigned to them', function (): void {
    expect(ClinicalReportAccess::currentDoctorId())->toBe(12);

    $encounterIds = IPDResource::getEloquentQuery()->pluck('encounter_id')->all();

    expect($encounterIds)->toBe(['IPD-MINE'])
        ->and(IPDResource::canEdit(new IPD(['doctor_id' => 12])))->toBeTrue()
        ->and(IPDResource::canEdit(new IPD(['doctor_id' => 15])))->toBeFalse();
});

