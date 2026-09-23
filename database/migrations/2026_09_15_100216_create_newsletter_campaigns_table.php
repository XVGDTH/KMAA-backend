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
    Schema::create('newsletter_campaigns', function (Blueprint $table) {
        $table->id();
        $table->string('subject');                       // Objet de l'email
        $table->text('intro')->nullable();               // Phrase d'accroche
        $table->longText('content');                     // Corps (RichEditor HTML)
        $table->string('status')->default('draft');      // draft|sending|sent
        $table->unsignedInteger('sent_count')->default(0);
        $table->timestamp('sent_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('newsletter_campaigns');
}
};
