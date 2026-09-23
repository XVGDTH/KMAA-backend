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
    Schema::table('blog_posts', function (Blueprint $table) {
        $table->string('kind')->default('article')->after('category');     // article|guide|actualite|conseil
        $table->json('tags')->nullable()->after('read_time');
        $table->string('author_name')->nullable()->after('author_id');     // nom affiché (sans compte user)
        $table->string('author_role')->nullable()->after('author_name');
        $table->string('author_avatar')->nullable()->after('author_role');
        $table->boolean('popular')->default(false)->after('featured');
        $table->string('level')->nullable()->after('popular');             // Débutant|Intermédiaire|Avancé
        $table->string('audience')->nullable()->after('level');
    });
}

public function down(): void
{
    Schema::table('blog_posts', function (Blueprint $table) {
        $table->dropColumn([
            'kind', 'tags', 'author_name', 'author_role',
            'author_avatar', 'popular', 'level', 'audience',
        ]);
    });
}
};
