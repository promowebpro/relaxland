<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_tags', function (Blueprint $table) {
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_post_id', 'blog_category_id']);
        });
        DB::table('blog_posts')->select('id', 'category_id')->orderBy('id')->chunkById(500, function ($posts) {
            DB::table('blog_post_tags')->insert($posts->map(fn ($post) => [
                'blog_post_id' => $post->id,
                'blog_category_id' => $post->category_id,
            ])->all());
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_tags');
    }
};
