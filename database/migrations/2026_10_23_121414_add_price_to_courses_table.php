<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedBigInteger('price')
                ->default(0)
                ->after('is_free')
                ->comment('Prix du cours en FCFA');

            $table->unsignedBigInteger('promo_price')
                ->nullable()
                ->after('price')
                ->comment('Prix promotionnel (si applicable)');

            $table->string('promo_code')
                ->nullable()
                ->after('promo_price')
                ->comment('Code promo associé');

            $table->text('long_description')
                ->nullable()
                ->after('description')
                ->comment('Description longue (HTML autorisé)');

            $table->string('provider')
                ->nullable()
                ->after('instructor_id')
                ->comment('Provider affiché (ex: KMAA Academy)');

            $table->string('language')
                ->default('Français')
                ->after('provider');

            $table->string('start_date')
                ->nullable()
                ->after('language');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'price',
                'promo_price',
                'promo_code',
                'long_description',
                'provider',
                'language',
                'start_date',
            ]);
        });
    }
};