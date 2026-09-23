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
    Schema::create('job_offers', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('type');                     // cdi|cdd|stage|freelance|partenaire
        $table->string('location')->nullable();
        $table->string('department')->nullable();
        $table->text('excerpt');
        $table->longText('description')->nullable();  // "Le poste"
        $table->json('missions')->nullable();          // liste à puces
        $table->json('profile')->nullable();           // liste à puces
        $table->json('tags')->nullable();
        $table->string('image')->nullable();
        $table->string('image_alt')->nullable();
        $table->boolean('featured')->default(false);
        $table->boolean('published')->default(true);
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('job_offers');
}
};
