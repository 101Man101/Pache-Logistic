<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_number', 36)->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('ppn_amount', 15, 2);
            $table->decimal('pph23_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->date('due_date');
            $table->string('status', 20)->default('unpaid')->index(); // unpaid, paid, overdue
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('invoice_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('shipment_id')->unique()->constrained('shipments');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });

        // Immutable financial journal (BR-04) — Strictly Non-COD channels only
        Schema::create('financial_ledger', function (Blueprint $table): void {
            $table->id();
            $table->string('entry_code', 40)->unique();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('channel', 24)->index(); // online, cash, top_invoice
            $table->string('direction', 10); // debit, credit
            $table->decimal('amount', 15, 2);
            $table->string('reference_type', 40);
            $table->unsignedBigInteger('reference_id');
            $table->string('description');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['channel', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_ledger');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
    }
};
