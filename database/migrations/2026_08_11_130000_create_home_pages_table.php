<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_pages', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_active')->default(false)->index();
            $table->string('hero_eyebrow')->nullable();
            $table->string('hero_title');
            $table->text('hero_description')->nullable();
            $table->string('hero_price')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_image_mobile')->nullable();
            $table->string('hero_image_alt')->nullable();
            $table->string('intro_title')->nullable();
            $table->text('intro_text')->nullable();
            $table->string('atmosphere_title')->nullable();
            $table->text('atmosphere_text')->nullable();
            $table->string('atmosphere_image')->nullable();
            $table->string('atmosphere_image_alt')->nullable();
            $table->string('care_title')->nullable();
            $table->text('care_text')->nullable();
            $table->string('seasons_title')->nullable();
            $table->text('seasons_text')->nullable();
            $table->string('cta_title')->nullable();
            $table->text('cta_text')->nullable();
            $table->string('genplan_title')->nullable();
            $table->text('genplan_text')->nullable();
            $table->string('genplan_image')->nullable();
            $table->string('genplan_image_alt')->nullable();
            $table->string('purchase_title')->nullable();
            $table->text('purchase_text')->nullable();
            $table->string('stories_title')->nullable();
            $table->text('stories_text')->nullable();
            $table->string('visit_title')->nullable();
            $table->text('visit_text')->nullable();
            $table->string('developer_title')->nullable();
            $table->text('developer_text')->nullable();
            $table->string('developer_image')->nullable();
            $table->string('developer_image_alt')->nullable();
            $table->string('blog_title')->nullable();
            $table->text('blog_text')->nullable();
            $table->json('benefits')->nullable();
            $table->json('life_scenarios')->nullable();
            $table->json('care_items')->nullable();
            $table->json('seasons')->nullable();
            $table->json('purchase_options')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_pages');
    }
};
