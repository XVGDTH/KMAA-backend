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
    // Si c'est un ENUM
    DB::statement("ALTER TABLE payments MODIFY provider ENUM('orange', 'moov', 'coris', 'bon') NOT NULL");

    // OU si c'est un VARCHAR trop court
    // Schema::table('payments', function (Blueprint $table) {
    //     $table->string('provider', 50)->change();
    // });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    DB::statement("ALTER TABLE payments MODIFY provider ENUM('orange', 'moov', 'coris') NOT NULL");
}
};
