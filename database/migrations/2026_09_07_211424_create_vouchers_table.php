<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::create('vouchers', function (Blueprint $table) {
        //     $table->id();
        //     $table->timestamps();
        // });
        Schema::create('vouchers', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete(); // null = valable sur tous les cours
    $table->unsignedInteger('max_uses')->default(1);
    $table->unsignedInteger('used_count')->default(0);
    $table->boolean('active')->default(true);
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
