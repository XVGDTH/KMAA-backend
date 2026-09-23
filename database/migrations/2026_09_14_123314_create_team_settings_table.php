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
    Schema::create('team_settings', function (Blueprint $table) {
        $table->id();
        $table->string('hero_eyebrow')->nullable();
        $table->string('hero_title')->nullable();           // "Une équipe d'experts,"
        $table->string('hero_title_accent')->nullable();    // "une vision partagée" (partie dorée)
        $table->text('hero_lead')->nullable();
        $table->json('stats')->nullable();                  // [{value:"12+", label:"experts terrain"}]
        $table->string('culture_title')->nullable();
        $table->text('culture_text')->nullable();
        $table->json('culture_values')->nullable();         // [{number,title,text}]
        $table->string('cta_title')->nullable();
        $table->text('cta_text')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('team_settings');
}
};
