<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            // ID de la réunion Zoom
            $table->string('zoom_meeting_id')->nullable()->after('room_name');

            // URL de démarrage (hôte formateur)
            $table->text('zoom_start_url')->nullable()->after('zoom_meeting_id');

            // URL de participation (apprenants)
            $table->text('zoom_join_url')->nullable()->after('zoom_start_url');

            // Mot de passe de la réunion (optionnel, pour sécurité renforcée)
            $table->string('zoom_password')->nullable()->after('zoom_join_url');
        });
    }

    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'zoom_meeting_id',
                'zoom_start_url',
                'zoom_join_url',
                'zoom_password',
            ]);
        });
    }
};