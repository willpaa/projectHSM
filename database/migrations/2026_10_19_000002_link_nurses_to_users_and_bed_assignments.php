<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nurses')) {
            Schema::table('nurses', function (Blueprint $table): void {
                if (! Schema::hasColumn('nurses', 'user_id')) {
                    $table->foreignId('user_id')
                        ->nullable()
                        ->after('nurse_id')
                        ->constrained('users')
                        ->nullOnDelete();
                    $table->unique('user_id');
                }

                if (! Schema::hasColumn('nurses', 'role')) {
                    $table->string('role', 40)->default('nurse')->after('user_id');
                }
            });

            if (Schema::hasTable('users') && Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
                DB::statement(<<<'SQL'
                    UPDATE nurses
                    INNER JOIN users ON LOWER(users.email) = LOWER(nurses.email)
                    SET nurses.user_id = users.id,
                        nurses.role = CASE WHEN EXISTS (
                            SELECT 1
                            FROM model_has_roles
                            INNER JOIN roles ON roles.id = model_has_roles.role_id
                            WHERE model_has_roles.model_id = users.id
                                AND model_has_roles.model_type = 'App\\Models\\User'
                                AND roles.guard_name = 'web'
                                AND roles.name = 'triage_nurse'
                        ) THEN 'triage_nurse' ELSE 'nurse' END
                    WHERE nurses.user_id IS NULL
                        AND EXISTS (
                            SELECT 1
                            FROM model_has_roles
                            INNER JOIN roles ON roles.id = model_has_roles.role_id
                            WHERE model_has_roles.model_id = users.id
                                AND model_has_roles.model_type = 'App\\Models\\User'
                                AND roles.guard_name = 'web'
                                AND roles.name IN ('nurse', 'triage_nurse')
                        )
                SQL);
            }
        }

        if (Schema::hasTable('beds')) {
            Schema::table('beds', function (Blueprint $table): void {
                if (! Schema::hasColumn('beds', 'assigned_nurse_id')) {
                    $table->foreignId('assigned_nurse_id')
                        ->nullable()
                        ->after('assigned_nurse')
                        ->constrained('nurses')
                        ->nullOnDelete();
                }
            });

            if (Schema::hasTable('nurses')) {
                DB::table('beds')
                    ->join('nurses', 'nurses.nurse_id', '=', 'beds.assigned_nurse')
                    ->whereNull('beds.assigned_nurse_id')
                    ->update(['beds.assigned_nurse_id' => DB::raw('nurses.id')]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('beds') && Schema::hasColumn('beds', 'assigned_nurse_id')) {
            Schema::table('beds', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('assigned_nurse_id');
            });
        }

        if (Schema::hasTable('nurses')) {
            Schema::table('nurses', function (Blueprint $table): void {
                if (Schema::hasColumn('nurses', 'user_id')) {
                    $table->dropConstrainedForeignId('user_id');
                }

                if (Schema::hasColumn('nurses', 'role')) {
                    $table->dropColumn('role');
                }
            });
        }
    }
};
