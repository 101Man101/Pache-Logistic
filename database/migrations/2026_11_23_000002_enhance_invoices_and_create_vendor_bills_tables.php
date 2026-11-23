<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('invoice_type', 20)->default('b2b_top')->after('invoice_number')->index();
            $table->boolean('apply_ppn')->default(true)->after('period_end');
            $table->boolean('apply_pph23')->default(true)->after('apply_ppn');
            $table->text('notes')->nullable()->after('due_date');
            $table->string('payment_reference', 80)->nullable()->after('paid_at');
        });

        Schema::table('invoice_lines', function (Blueprint $table): void {
            $table->unsignedBigInteger('shipment_id')->nullable()->change();
            $table->string('description')->nullable()->after('shipment_id');
            $table->decimal('quantity', 10, 2)->default(1)->after('description');
            $table->string('unit', 24)->default('resi')->after('quantity');
            $table->decimal('unit_price', 15, 2)->default(0)->after('unit');
        });

        Schema::create('vendor_bills', function (Blueprint $table): void {
            $table->id();
            $table->string('bill_number', 36)->unique();
            $table->string('vendor_invoice_no', 64)->nullable()->index();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('manifest_id')->nullable()->constrained('manifests')->nullOnDelete();
            $table->date('bill_date');
            $table->date('due_date')->index();
            $table->decimal('subtotal', 15, 2);
            $table->boolean('apply_ppn')->default(false);
            $table->boolean('apply_pph23')->default(false);
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->decimal('pph23_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('status', 20)->default('unpaid')->index(); // unpaid, paid, overdue
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference', 80)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('vendor_bill_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_bill_id')->constrained('vendor_bills')->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 24)->default('trip');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_bill_lines');
        Schema::dropIfExists('vendor_bills');

        Schema::table('invoice_lines', function (Blueprint $table): void {
            $table->dropColumn(['description', 'quantity', 'unit', 'unit_price']);
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn([
                'invoice_type',
                'apply_ppn',
                'apply_pph23',
                'notes',
                'payment_reference',
            ]);
        });
    }
};
