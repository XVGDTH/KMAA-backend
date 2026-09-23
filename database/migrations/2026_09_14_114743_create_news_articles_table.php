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
    Schema::create('news_articles', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique();
        $table->string('title');
        $table->text('excerpt');
        $table->longText('content')->nullable();
        $table->string('category');                     // formation|entreprise|evenement|ressources|vie-kmaa|carriere
        $table->string('image')->nullable();
        $table->string('image_alt')->nullable();
        $table->string('author_name')->nullable();
        $table->string('author_role')->nullable();
        $table->string('author_avatar')->nullable();
        $table->unsignedInteger('read_time')->default(4);   // minutes
        $table->boolean('featured')->default(false);
        $table->boolean('published')->default(true);
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('news_articles');
}
};
