<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('patients')) {
            Schema::create('patients', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
                $table->string('patient_id')->unique();
                $table->string('mrn')->unique();
                $table->string('full_name');
                $table->enum('gender', ['male', 'female', 'other']);
                $table->date('dob')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable()->unique();
                $table->text('address')->nullable();
                $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
                $table->text('allergies')->nullable();
                $table->string('emergency_contact')->nullable();
                $table->string('insurance_id')->nullable();
                $table->timestamp('registered_at')->nullable();
                $table->enum('status', ['active', 'today', 'inactive', 'deceased'])->default('active');
                $table->timestamps();
            });

            return;
        }

        Schema::table('patients', function (Blueprint $table): void {
            if (! Schema::hasColumn('patients', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('patients', 'patient_id')) {
                $table->string('patient_id')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('patients', 'mrn')) {
                $table->string('mrn')->nullable()->after('patient_id');
            }

            if (! Schema::hasColumn('patients', 'full_name')) {
                $table->string('full_name')->nullable()->after('mrn');
            }

            if (! Schema::hasColumn('patients', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('full_name');
            }

            if (! Schema::hasColumn('patients', 'dob')) {
                $table->date('dob')->nullable()->after('gender');
            }

            if (! Schema::hasColumn('patients', 'phone')) {
                $table->string('phone')->nullable()->after('dob');
            }

            if (! Schema::hasColumn('patients', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('patients', 'address')) {
                $table->text('address')->nullable()->after('email');
            }

            if (! Schema::hasColumn('patients', 'blood_group')) {
                $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable()->after('address');
            }

            if (! Schema::hasColumn('patients', 'allergies')) {
                $table->text('allergies')->nullable()->after('blood_group');
            }

            if (! Schema::hasColumn('patients', 'emergency_contact')) {
                $table->string('emergency_contact')->nullable()->after('allergies');
            }

            if (! Schema::hasColumn('patients', 'insurance_id')) {
                $table->string('insurance_id')->nullable()->after('emergency_contact');
            }

            if (! Schema::hasColumn('patients', 'registered_at')) {
                $table->timestamp('registered_at')->nullable()->after('insurance_id');
            }

            if (! Schema::hasColumn('patients', 'status')) {
                $table->enum('status', ['active', 'today', 'inactive', 'deceased'])->default('active')->after('registered_at');
            }

            if (! Schema::hasColumn('patients', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('patients', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (! Schema::hasIndex('patients', 'patients_patient_id_unique', 'unique')) {
            Schema::table('patients', function (Blueprint $table): void {
                $table->unique('patient_id');
            });
        }

        if (! Schema::hasIndex('patients', 'patients_mrn_unique', 'unique')) {
            Schema::table('patients', function (Blueprint $table): void {
                $table->unique('mrn');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This repair migration may operate on an existing patient table; dropping its columns would destroy patient data.
    }
};
