<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table): void {
            $table->id();
            $table->string('awb_number', 32)->unique();
            $table->string('reference_no', 64)->nullable()->index();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('origin_branch_id')->constrained('branches');
            $table->foreignId('destination_branch_id')->constrained('branches');

            $table->string('shipper_name');
            $table->string('shipper_phone', 30);
            $table->text('shipper_address');

            $table->string('consignee_name');
            $table->string('consignee_phone', 30)->index();
            $table->text('consignee_address');

            $table->string('service_type', 12)->default('REG'); // REG, EXP, CRG
            $table->unsignedInteger('volumetric_divisor')->default(6000);
            $table->decimal('actual_weight_kg', 10, 3);
            $table->decimal('volumetric_weight_kg', 10, 3);
            $table->decimal('chargeable_weight_kg', 10, 3);

            $table->decimal('shipping_cost', 15, 2);
            $table->decimal('insurance_cost', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);

            // Strictly Non-COD: online, cash, top ONLY
            $table->string('payment_method', 16)->index();
            $table->string('payment_status', 16)->default('pending')->index(); // pending, paid, billed
            $table->string('payment_reference', 80)->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->string('status', 24)->default('booked')->index();
            $table->timestamp('delivered_at')->nullable();

            // Mandatory e-POD fields (BR-07)
            $table->string('epod_receiver_name')->nullable();
            $table->string('epod_receiver_relation', 40)->nullable();
            $table->decimal('epod_latitude', 10, 7)->nullable();
            $table->decimal('epod_longitude', 10, 7)->nullable();
            $table->text('epod_notes')->nullable();
            $table->string('epod_photo_url')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('shipment_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('actual_weight_kg', 10, 3);
            $table->decimal('length_cm', 10, 2);
            $table->decimal('width_cm', 10, 2);
            $table->decimal('height_cm', 10, 2);
            $table->timestamps();
        });

        Schema::create('tracking_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('status', 24);
            $table->string('location');
            $table->string('description');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['shipment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_logs');
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
    }
};
