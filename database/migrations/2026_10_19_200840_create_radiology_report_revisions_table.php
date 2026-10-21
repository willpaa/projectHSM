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
        Schema::create('radiology_report_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radiology_id')->constrained('radiologies')->cascadeOnDelete();
            $table->unsignedSmallInteger('revision_number');
            $table->string('revision_type', 20);
            $table->text('reason')->nullable();
            $table->json('report_data');
            $table->foreignId('authored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unique(['radiology_id', 'revision_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_report_revisions');
    }
};
