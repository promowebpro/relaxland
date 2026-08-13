<?php

use App\Domain\Genplan\PlotStatus;
use App\Domain\Genplan\QuarterStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('genplans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image_3d');
            $table->string('image_2d');
            $table->string('mobile_image_3d')->nullable();
            $table->string('mobile_image_2d')->nullable();
            $table->unsignedInteger('original_width')->nullable();
            $table->unsignedInteger('original_height')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('quarters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('genplan_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status', 32)->default(QuarterStatus::Available->value)->index();
            $table->json('polygon_data');
            $table->decimal('label_x', 7, 6)->nullable();
            $table->decimal('label_y', 7, 6)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();

            $table->unique(['genplan_id', 'slug']);
            $table->index(['genplan_id', 'is_active', 'sort_order']);
        });

        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarter_id')->constrained()->restrictOnDelete();
            $table->string('number', 64);
            $table->string('slug');
            $table->decimal('area', 10, 2);
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('price_per_sotka', 15, 2)->nullable();
            $table->string('status', 32)->default(PlotStatus::Available->value)->index();
            $table->json('polygon_data')->nullable();
            $table->decimal('marker_x', 7, 6)->nullable();
            $table->decimal('marker_y', 7, 6)->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('attributes')->nullable();
            $table->boolean('is_visible')->default(false)->index();
            $table->timestamps();

            $table->unique(['quarter_id', 'slug']);
            $table->unique(['quarter_id', 'number']);
            $table->index(['quarter_id', 'is_visible', 'status']);
        });

        Schema::create('infrastructure_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('genplan_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('category', 64)->index();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('marker_x', 7, 6);
            $table->decimal('marker_y', 7, 6);
            $table->boolean('show_on_3d')->default(true);
            $table->boolean('show_on_2d')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();

            $table->index(['genplan_id', 'is_active', 'sort_order']);
        });

        Schema::create('surrounding_places', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 64)->index();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 11, 7);
            $table->text('description')->nullable();
            $table->string('external_url', 2048)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surrounding_places');
        Schema::dropIfExists('infrastructure_points');
        Schema::dropIfExists('plots');
        Schema::dropIfExists('quarters');
        Schema::dropIfExists('genplans');
    }
};
