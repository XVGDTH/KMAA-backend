<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enterprise_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('enterprise_pricing_plans');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');

            $table->string('company_name');
            $table->string('company_email');
            $table->string('company_phone')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_tax_id')->nullable();

            $table->unsignedInteger('seats_total');
            $table->unsignedInteger('seats_used')->default(1);
            $table->unsignedBigInteger('price_per_seat');
            $table->unsignedBigInteger('total_paid');
            $table->unsignedBigInteger('discount_applied')->default(0);
            $table->string('promo_code')->nullable();

            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');

            // ✅ nullable pour éviter l'erreur MySQL
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index('owner_id');
        });

        // Membres d'une licence
        Schema::create('enterprise_license_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('enterprise_licenses')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->enum('status', ['invite', 'active', 'revoked'])->default('invite');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['license_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_license_members');
        Schema::dropIfExists('enterprise_licenses');
    }
};