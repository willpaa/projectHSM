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
        Schema::table('inventory_transactions', function (Blueprint $table): void {
            $table->index(['created_at', 'operation_type'], 'inv_tx_created_operation_idx');
        });

        Schema::table('pharmacy_sales', function (Blueprint $table): void {
            $table->index(['sold_at', 'sale_type'], 'pharmacy_sales_sold_type_idx');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->index('payment_date', 'payments_payment_date_idx');
        });

        Schema::table('prescriptions', function (Blueprint $table): void {
            $table->index('created_at', 'prescriptions_created_at_idx');
        });

        Schema::table('patients', function (Blueprint $table): void {
            $table->index('created_at', 'patients_created_at_idx');
        });

        Schema::table('service_requests', function (Blueprint $table): void {
            $table->index(['payment_status', 'requested_at'], 'service_requests_status_requested_idx');
            $table->index(['payment_status', 'paid_at'], 'service_requests_status_paid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table): void {
            $table->dropIndex('service_requests_status_paid_idx');
            $table->dropIndex('service_requests_status_requested_idx');
        });

        Schema::table('patients', function (Blueprint $table): void {
            $table->dropIndex('patients_created_at_idx');
        });

        Schema::table('prescriptions', function (Blueprint $table): void {
            $table->dropIndex('prescriptions_created_at_idx');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex('payments_payment_date_idx');
        });

        Schema::table('pharmacy_sales', function (Blueprint $table): void {
            $table->dropIndex('pharmacy_sales_sold_type_idx');
        });

        Schema::table('inventory_transactions', function (Blueprint $table): void {
            $table->dropIndex('inv_tx_created_operation_idx');
        });
    }
};
