<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('code', 24)->unique();
            $table->string('name');
            $table->string('zone_type', 24)->default('dry'); // dry, cold, staging
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('storage_bins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('code', 40)->unique(); // DRY-A1-R1-B01
            $table->string('zone', 24);
            $table->string('aisle', 16);
            $table->string('rack', 16);
            $table->string('bin', 16);
            $table->unsignedInteger('max_capacity')->default(500);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('sku', 48)->unique();
            $table->string('name');
            $table->string('allocation_rule', 8)->default('FIFO'); // FIFO, FEFO
            $table->string('unit', 20)->default('pcs');
            $table->timestamps();
        });

        // Immutable append-only ledger (BR-04)
        Schema::create('inventory_ledger', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('storage_bin_id')->constrained('storage_bins');
            $table->foreignId('product_id')->constrained('products');
            $table->string('movement_type', 24); // inbound, outbound, adjustment
            $table->string('reference_code', 64);
            $table->integer('quantity_change');
            $table->integer('balance_after');
            $table->string('batch_number', 48)->nullable();
            $table->date('expires_at')->nullable();
            $table->string('reason')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['warehouse_id', 'storage_bin_id', 'product_id', 'id'], 'inv_ledger_bin_product_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_ledger');
        Schema::dropIfExists('products');
        Schema::dropIfExists('storage_bins');
        Schema::dropIfExists('warehouses');
    }
};
