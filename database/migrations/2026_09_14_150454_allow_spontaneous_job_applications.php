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
    // La foreign key gêne le change() → on la retire puis on la recrée
    Schema::table('job_applications', function (Blueprint $table) {
        $table->dropForeign(['job_offer_id']);
    });

    Schema::table('job_applications', function (Blueprint $table) {
        $table->unsignedBigInteger('job_offer_id')->nullable()->change();
        $table->foreign('job_offer_id')->references('id')->on('job_offers')->nullOnDelete();

        $table->string('type')->default('offre')->after('id');   // offre|spontanee
        $table->string('full_name')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('job_applications', function (Blueprint $table) {
        $table->dropForeign(['job_offer_id']);
        $table->string('full_name')->nullable(false)->change();
    });

    Schema::table('job_applications', function (Blueprint $table) {
        $table->dropColumn('type');
        $table->foreignId('job_offer_id')->change();
    });
}
};
