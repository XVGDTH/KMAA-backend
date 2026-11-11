<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['course', 'enterprise'])->default('course');

            $table->string('reference')->unique();          // "KMAA-2026-000123"
            $table->string('invoice_number')->unique();     // "FAC-2026-00001"

            // Détails finance
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('tva')->default(0);
            $table->unsignedBigInteger('total');
            $table->string('currency')->default('FCFA');
            $table->string('promo_code')->nullable();

            // Paiement
            $table->string('payment_method');
            $table->string('payment_method_label');
            $table->string('transaction_id');

            // Client
            $table->string('customer_name');
            $table->string('customer_email');

            // Statut
            $table->enum('status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};