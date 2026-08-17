<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();          // ZL-C-0001

            $table->string('name');                        // اسم المنشأة
            $table->string('contact_name')->nullable();    // المسؤول
            $table->string('business_type', 60)->nullable();
            $table->string('phone', 32);
            $table->string('alt_phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('governorate', 60)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('tax_id', 40)->nullable();

            // commercial terms
            $table->decimal('price_per_pack', 10, 2)->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('opening_balance', 12, 2)->default(0); // موجب = عليه لينا
            $table->unsignedSmallInteger('payment_terms_days')->default(0);

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
            $table->index(['is_active', 'name']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });
        Schema::dropIfExists('customers');
    }
};
