<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('plate_number', 24)->unique();
            $table->string('type', 40); // Blind Van, Engkel Box, CDD Box, Fuso Wingbox
            $table->decimal('capacity_kg', 10, 2);
            $table->decimal('capacity_cbm', 8, 2);
            $table->date('stnk_expires_at');
            $table->date('kir_expires_at');
            $table->string('status', 20)->default('available'); // available, on_trip, maintenance
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 30);
            $table->string('sim_number', 40)->unique();
            $table->date('sim_expires_at');
            $table->string('status', 20)->default('available');
            $table->timestamps();
        });

        Schema::create('manifests', function (Blueprint $table): void {
            $table->id();
            $table->string('manifest_number', 32)->unique();
            $table->foreignId('origin_branch_id')->constrained('branches');
            $table->foreignId('destination_branch_id')->constrained('branches');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('driver_id')->constrained('drivers');
            $table->string('seal_number', 40)->nullable();
            $table->decimal('driver_advance_amount', 15, 2)->default(0);
            $table->string('status', 20)->default('open'); // open, departed, arrived
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('manifest_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manifest_id')->constrained('manifests')->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamp('unloaded_at')->nullable();

            $table->unique(['manifest_id', 'shipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manifest_items');
        Schema::dropIfExists('manifests');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
    }
};
