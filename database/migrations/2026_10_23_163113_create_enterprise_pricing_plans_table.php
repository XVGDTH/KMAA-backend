<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enterprise_pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedBigInteger('price_per_seat');
            $table->unsignedInteger('min_seats')->default(1);
            $table->unsignedInteger('max_seats')->default(100);
            $table->string('badge')->nullable();
            $table->text('description')->nullable();
            $table->json('features');             // Liste des fonctionnalités
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_pricing_plans');
    }
};