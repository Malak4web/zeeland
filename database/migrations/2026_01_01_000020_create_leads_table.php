<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // what the visitor typed
            $table->string('name');
            $table->string('business_name')->nullable();
            $table->string('business_type', 60)->nullable();
            $table->string('phone', 32);
            $table->string('email')->nullable();
            $table->string('governorate', 60)->nullable();
            $table->string('monthly_volume', 60)->nullable();
            $table->text('message')->nullable();

            // pipeline
            $table->string('status', 20)->default('new');   // new|contacted|quoted|won|lost
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable();   // set once converted
            $table->string('source', 30)->default('landing_form');
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('lost_reason')->nullable();

            // where it came from — this is what tells us which blog post pays off
            $table->string('page_url', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('utm_source', 120)->nullable();
            $table->string('utm_medium', 120)->nullable();
            $table->string('utm_campaign', 120)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index('phone');
        });

        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_notes');
        Schema::dropIfExists('leads');
    }
};
