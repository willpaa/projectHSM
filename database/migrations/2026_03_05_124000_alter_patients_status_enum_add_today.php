<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE patients MODIFY COLUMN status ENUM('active','today','inactive','deceased') NOT NULL DEFAULT 'active'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE patients MODIFY COLUMN status ENUM('active','inactive','deceased') NOT NULL DEFAULT 'active'"
        );
    }
};
