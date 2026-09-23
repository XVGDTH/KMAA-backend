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
    Schema::table('pricing_plans', function (Blueprint $table) {
        $table->unsignedTinyInteger('level')->default(1)->after('badge'); // 1 à 4
    });
}

public function down(): void
{
    Schema::table('pricing_plans', function (Blueprint $table) {
        $table->dropColumn('level');
    });
}
};
