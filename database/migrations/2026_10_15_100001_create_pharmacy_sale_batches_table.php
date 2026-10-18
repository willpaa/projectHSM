<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pharmacy_sale_batches')) {
            return;
        }

        Schema::create('pharmacy_sale_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacy_sale_id')->index();
            $table->unsignedBigInteger('pharmacy_batch_id')->index();
            $table->integer('quantity');
            // Cost of the goods actually dispensed from this batch (per-unit).
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('cost_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('pharmacy_sale_id')
                ->references('id')->on('pharmacy_sales')
                ->cascadeOnDelete();
            $table->foreign('pharmacy_batch_id')
                ->references('id')->on('pharmacy_batches')
                ->restrictOnDelete();
        });

        // Optional but useful: record profit snapshot on the sale line itself
        // so reports do not have to join allocations every time.
        Schema::table('pharmacy_sales', function (Blueprint $table) {
            if (! Schema::hasColumn('pharmacy_sales', 'cogs_amount')) {
                $table->decimal('cogs_amount', 12, 2)->default(0)->after('total_amount');
            }
            if (! Schema::hasColumn('pharmacy_sales', 'profit_amount')) {
                $table->decimal('profit_amount', 12, 2)->default(0)->after('cogs_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_sales', function (Blueprint $table) {
            if (Schema::hasColumn('pharmacy_sales', 'profit_amount')) {
                $table->dropColumn('profit_amount');
            }
            if (Schema::hasColumn('pharmacy_sales', 'cogs_amount')) {
                $table->dropColumn('cogs_amount');
            }
        });

        Schema::dropIfExists('pharmacy_sale_batches');
    }
};
