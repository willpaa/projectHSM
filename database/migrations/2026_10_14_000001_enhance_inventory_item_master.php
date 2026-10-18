<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table): void {
            $table->string('sku_code', 50)->nullable()->unique()->after('item_id');
            $table->string('barcode', 50)->nullable()->index()->after('sku_code');
            $table->unsignedInteger('pack_size')->default(1)->after('unit');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('selling_price');
            $table->boolean('is_active')->default(true)->after('storage_condition');
            $table->unsignedBigInteger('supplier_ref_id')->nullable()->after('supplier_id');
        });

        // Best-effort backfill: link existing free-text supplier ids to real
        // supplier records where the code/name matches.
        DB::table('suppliers')->get()->each(function ($supplier): void {
            DB::table('inventories')
                ->where('supplier_id', (string) $supplier->supplier_code)
                ->orWhere('supplier_id', (string) $supplier->name)
                ->update(['supplier_ref_id' => $supplier->id]);
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table): void {
            $table->dropIndex(['barcode']);
            $table->dropUnique(['sku_code']);
            $table->dropColumn(['sku_code', 'barcode', 'pack_size', 'tax_rate', 'is_active', 'supplier_ref_id']);
        });
    }
};
