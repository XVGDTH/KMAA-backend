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
    Schema::create('team_members', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('role');
        $table->string('department');               // direction|conseil|formation|support
        $table->text('bio')->nullable();
        $table->string('image')->nullable();
        $table->string('image_alt')->nullable();
        $table->string('linkedin')->nullable();
        $table->boolean('featured')->default(false);
        $table->unsignedInteger('position')->default(1);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('team_members');
}
};
