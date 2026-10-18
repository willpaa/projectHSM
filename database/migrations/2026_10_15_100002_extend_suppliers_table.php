<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'tin_number')) {
                $table->string('tin_number', 50)->nullable()->after('supplier_code');
            }
            if (! Schema::hasColumn('suppliers', 'license_number')) {
                $table->string('license_number', 80)->nullable()->after('tin_number');
            }
            if (! Schema::hasColumn('suppliers', 'license_expires_at')) {
                $table->date('license_expires_at')->nullable()->after('license_number');
            }
            if (! Schema::hasColumn('suppliers', 'payment_terms')) {
                $table->string('payment_terms', 40)->default('cod')->after('status');
            }
            if (! Schema::hasColumn('suppliers', 'credit_limit')) {
                $table->decimal('credit_limit', 14, 2)->default(0)->after('payment_terms');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            foreach (['credit_limit', 'payment_terms', 'license_expires_at', 'license_number', 'tin_number'] as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
