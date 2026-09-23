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
    Schema::table('sequence_progress', function (Blueprint $table) {
        $table->unsignedTinyInteger('score_percent')->nullable()->after('completed');
        $table->boolean('passed')->default(false)->after('score_percent');
        $table->integer('attempts')->default(0)->after('passed');
    });
}

public function down(): void
{
    Schema::table('sequence_progress', function (Blueprint $table) {
        $table->dropColumn(['score_percent', 'passed', 'attempts']);
    });
}
};
