<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tariffs', function (Blueprint $table): void {
            $table->decimal('fuel_surcharge_pct', 5, 2)->default(0)->after('insurance_rate_pct');
            $table->decimal('handling_fee', 15, 2)->default(0)->after('fuel_surcharge_pct');
            $table->decimal('heavy_weight_threshold_kg', 8, 2)->nullable()->after('handling_fee');
            $table->decimal('heavy_weight_surcharge_per_kg', 15, 2)->default(0)->after('heavy_weight_threshold_kg');
        });

        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 24)->unique();
            $table->string('name');
            $table->string('type', 30)->default('linehaul'); // linehaul, air_freight, sea_freight, last_mile, customs
            $table->string('contact_person')->nullable();
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->unsignedSmallInteger('payment_terms_days')->default(14);
            $table->decimal('base_cost_per_kg', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('surcharges', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 24)->unique();
            $table->string('name');
            $table->string('calc_mode', 16)->default('fixed'); // fixed, percentage, per_kg
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('shipments', function (Blueprint $table): void {
            $table->foreignId('vendor_id')->nullable()->after('destination_branch_id')->constrained('vendors')->nullOnDelete();
            $table->string('vendor_awb_no', 64)->nullable()->after('vendor_id');
            $table->decimal('vendor_cost', 15, 2)->default(0)->after('vendor_awb_no');
            $table->decimal('surcharge_cost', 15, 2)->default(0)->after('insurance_cost');
            $table->decimal('additional_charge_cost', 15, 2)->default(0)->after('surcharge_cost');
        });

        Schema::create('shipment_charges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('surcharge_id')->nullable()->constrained('surcharges')->nullOnDelete();
            $table->string('charge_type', 20)->default('add_charge'); // surcharge, add_charge
            $table->string('code', 32)->nullable();
            $table->string('description');
            $table->string('calc_mode', 16)->default('fixed'); // fixed, percentage, per_kg
            $table->decimal('rate_value', 15, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('amount', 15, 2);
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['shipment_id', 'charge_type']);
        });

        Schema::table('manifests', function (Blueprint $table): void {
            $table->foreignId('vendor_id')->nullable()->after('driver_id')->constrained('vendors')->nullOnDelete();
            $table->decimal('vendor_cost', 15, 2)->default(0)->after('driver_advance_amount');
        });
    }

    public function down(): void
    {
        Schema::table('manifests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn('vendor_cost');
        });

        Schema::dropIfExists('shipment_charges');

        Schema::table('shipments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn([
                'vendor_awb_no',
                'vendor_cost',
                'surcharge_cost',
                'additional_charge_cost',
            ]);
        });

        Schema::dropIfExists('surcharges');
        Schema::dropIfExists('vendors');

        Schema::table('tariffs', function (Blueprint $table): void {
            $table->dropColumn([
                'fuel_surcharge_pct',
                'handling_fee',
                'heavy_weight_threshold_kg',
                'heavy_weight_surcharge_per_kg',
            ]);
        });
    }
};
