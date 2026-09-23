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
    Schema::table('courses', function (Blueprint $table) {
        $table->string('level')->nullable()->change();
        $table->string('subject')->nullable()->change();
        $table->string('category')->nullable()->change();
        $table->string('diploma')->nullable()->change();
        $table->string('duration_full')->nullable()->change();
        $table->string('duration_alternance')->nullable()->change();
        $table->text('description')->nullable()->change();
        $table->string('image')->nullable()->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //
        });
    }
};
