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
    Schema::create('job_applications', function (Blueprint $table) {
        $table->id();
        $table->foreignId('job_offer_id')->constrained('job_offers')->cascadeOnDelete();
        $table->string('full_name');
        $table->string('email');
        $table->string('phone')->nullable();
        $table->text('message')->nullable();
        $table->string('cv_path')->nullable();          // /storage/candidatures/xxx.pdf
        $table->string('status')->default('nouvelle');  // nouvelle|vue|retenu|refuse
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('job_applications');
}
};
