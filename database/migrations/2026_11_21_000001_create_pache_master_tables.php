<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $postgisAvailable = DB::selectOne("SELECT 1 FROM pg_available_extensions WHERE name = 'postgis'");
            if ($postgisAvailable !== null) {
                DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
            }
        }

        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('type', 30)->default('regional_hub'); // head_office, regional_hub, sorting_center, drop_point
            $table->string('city', 100);
            $table->text('address');
            $table->string('phone', 30)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            $table->string('role', 40)->default('admin_oms')->after('email');
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 24)->unique();
            $table->string('name');
            $table->string('type', 24)->default('retail'); // retail, b2b_contract
            $table->string('npwp', 32)->nullable();
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->text('address');
            $table->boolean('is_top_enabled')->default(false);
            $table->unsignedSmallInteger('top_days')->default(0); // 7, 14, 30, 45
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tariffs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('origin_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('destination_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('service_type', 12); // REG, EXP, CRG
            $table->unsignedInteger('volumetric_divisor')->default(6000); // 6000, 5000, 4000
            $table->decimal('min_weight_kg', 8, 2)->default(1);
            $table->decimal('rate_per_kg', 15, 2);
            $table->decimal('insurance_rate_pct', 5, 3)->default(0.25);
            $table->unsignedSmallInteger('sla_days')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['origin_branch_id', 'destination_branch_id', 'service_type'], 'tariffs_route_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
        Schema::dropIfExists('customers');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn('role');
        });
        Schema::dropIfExists('branches');
    }
};
