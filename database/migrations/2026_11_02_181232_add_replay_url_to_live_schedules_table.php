<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_schedules', function (Blueprint $table) {
            $table->string('replay_url')->nullable()->after('reminder_sent');
        });
    }

    public function down(): void
    {
        Schema::table('live_schedules', function (Blueprint $table) {
            $table->dropColumn('replay_url');
        });
    }
};