<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 24)->unique();            // ZL-P-2026-0001
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            // nullable: a payment can settle the account rather than one order
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('method', 20)->default('cash');   // cash|bank|instapay|wallet|cheque
            $table->string('reference', 120)->nullable();
            $table->date('paid_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
