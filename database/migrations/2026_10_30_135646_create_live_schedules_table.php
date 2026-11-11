<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('users');
            $table->dateTime('scheduled_at'); // La date et l'heure choisie
            $table->string('zoom_meeting_id')->nullable();
            $table->string('zoom_join_url')->nullable();
            $table->string('zoom_start_url')->nullable();
            $table->string('zoom_password')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();
            $table->string('replay_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_schedules');
    }
};