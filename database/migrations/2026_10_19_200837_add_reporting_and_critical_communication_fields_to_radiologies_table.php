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
        Schema::table('radiologies', function (Blueprint $table) {
            $table->longText('report_technique')->nullable();
            $table->longText('comparison_studies')->nullable();
            $table->string('critical_contact_name')->nullable();
            $table->string('critical_contact_method', 40)->nullable();
            $table->timestamp('critical_contacted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('doctor_review_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radiologies', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by');
        });

        Schema::table('radiologies', function (Blueprint $table) {
            $table->dropColumn([
                'report_technique',
                'comparison_studies',
                'critical_contact_name',
                'critical_contact_method',
                'critical_contacted_at',
                'reviewed_at',
                'doctor_review_notes',
            ]);
        });
    }
};
