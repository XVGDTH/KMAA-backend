<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price'); // FCFA
            $table->string('price_note')->nullable();
            $table->text('description')->nullable();
            $table->boolean('featured')->default(false);
            $table->json('features')->nullable(); // [{label, included}]
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('amount');
            $table->enum('provider', ['orange', 'moov', 'coris']);
            $table->string('phone_number')->nullable();
            $table->enum('status', ['en_attente', 'paye', 'rembourse', 'echoue'])->default('en_attente');
            $table->string('transaction_reference')->nullable(); // TODO : id réel fourni par le fournisseur mobile money
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('pricing_plans');
    }
};