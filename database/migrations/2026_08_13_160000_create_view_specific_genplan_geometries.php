<?php

use App\Domain\Genplan\GenplanMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('genplans', function (Blueprint $table) {
            $table->boolean('mobile_image_3d_is_compatible')->default(false)->after('mobile_image_2d');
            $table->boolean('mobile_image_2d_is_compatible')->default(false)->after('mobile_image_3d_is_compatible');
        });

        Schema::create('quarter_geometries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarter_id')->constrained()->cascadeOnDelete();
            $table->enum('mode', array_column(GenplanMode::cases(), 'value'));
            $table->json('polygon_data');
            $table->decimal('label_x', 7, 6)->nullable();
            $table->decimal('label_y', 7, 6)->nullable();
            $table->timestamps();
            $table->unique(['quarter_id', 'mode']);
        });

        Schema::create('plot_geometries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained()->cascadeOnDelete();
            $table->enum('mode', array_column(GenplanMode::cases(), 'value'));
            $table->json('polygon_data')->nullable();
            $table->decimal('marker_x', 7, 6)->nullable();
            $table->decimal('marker_y', 7, 6)->nullable();
            $table->timestamps();
            $table->unique(['plot_id', 'mode']);
        });

        Schema::create('infrastructure_point_geometries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infrastructure_point_id')->constrained()->cascadeOnDelete();
            $table->enum('mode', array_column(GenplanMode::cases(), 'value'));
            $table->decimal('marker_x', 7, 6);
            $table->decimal('marker_y', 7, 6);
            $table->timestamps();
            $table->unique(['infrastructure_point_id', 'mode'], 'infrastructure_geometry_point_mode_unique');
        });

        $this->moveLegacyGeometryToThreeD();

        Schema::table('quarters', function (Blueprint $table) {
            $table->dropColumn(['polygon_data', 'label_x', 'label_y']);
        });
        Schema::table('plots', function (Blueprint $table) {
            $table->dropColumn(['polygon_data', 'marker_x', 'marker_y']);
        });
        Schema::table('infrastructure_points', function (Blueprint $table) {
            $table->dropColumn(['marker_x', 'marker_y']);
        });
    }

    public function down(): void
    {
        Schema::table('quarters', function (Blueprint $table) {
            $table->json('polygon_data')->nullable();
            $table->decimal('label_x', 7, 6)->nullable();
            $table->decimal('label_y', 7, 6)->nullable();
        });
        Schema::table('plots', function (Blueprint $table) {
            $table->json('polygon_data')->nullable();
            $table->decimal('marker_x', 7, 6)->nullable();
            $table->decimal('marker_y', 7, 6)->nullable();
        });
        Schema::table('infrastructure_points', function (Blueprint $table) {
            $table->decimal('marker_x', 7, 6)->nullable();
            $table->decimal('marker_y', 7, 6)->nullable();
        });

        DB::table('quarter_geometries')->where('mode', GenplanMode::ThreeD->value)->orderBy('id')->each(function ($geometry): void {
            DB::table('quarters')->where('id', $geometry->quarter_id)->update([
                'polygon_data' => $geometry->polygon_data,
                'label_x' => $geometry->label_x,
                'label_y' => $geometry->label_y,
            ]);
        });
        DB::table('plot_geometries')->where('mode', GenplanMode::ThreeD->value)->orderBy('id')->each(function ($geometry): void {
            DB::table('plots')->where('id', $geometry->plot_id)->update([
                'polygon_data' => $geometry->polygon_data,
                'marker_x' => $geometry->marker_x,
                'marker_y' => $geometry->marker_y,
            ]);
        });
        DB::table('infrastructure_point_geometries')->where('mode', GenplanMode::ThreeD->value)->orderBy('id')->each(function ($geometry): void {
            DB::table('infrastructure_points')->where('id', $geometry->infrastructure_point_id)->update([
                'marker_x' => $geometry->marker_x,
                'marker_y' => $geometry->marker_y,
            ]);
        });

        Schema::dropIfExists('infrastructure_point_geometries');
        Schema::dropIfExists('plot_geometries');
        Schema::dropIfExists('quarter_geometries');

        Schema::table('genplans', function (Blueprint $table) {
            $table->dropColumn(['mobile_image_3d_is_compatible', 'mobile_image_2d_is_compatible']);
        });
    }

    private function moveLegacyGeometryToThreeD(): void
    {
        $now = now();

        DB::table('quarters')->orderBy('id')->each(function ($quarter) use ($now): void {
            DB::table('quarter_geometries')->insert([
                'quarter_id' => $quarter->id,
                'mode' => GenplanMode::ThreeD->value,
                'polygon_data' => $quarter->polygon_data,
                'label_x' => $quarter->label_x,
                'label_y' => $quarter->label_y,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        DB::table('plots')->where(function ($query): void {
            $query->whereNotNull('polygon_data')->orWhereNotNull('marker_x')->orWhereNotNull('marker_y');
        })->orderBy('id')->each(function ($plot) use ($now): void {
            DB::table('plot_geometries')->insert([
                'plot_id' => $plot->id,
                'mode' => GenplanMode::ThreeD->value,
                'polygon_data' => $plot->polygon_data,
                'marker_x' => $plot->marker_x,
                'marker_y' => $plot->marker_y,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        DB::table('infrastructure_points')->orderBy('id')->each(function ($point) use ($now): void {
            DB::table('infrastructure_point_geometries')->insert([
                'infrastructure_point_id' => $point->id,
                'mode' => GenplanMode::ThreeD->value,
                'marker_x' => $point->marker_x,
                'marker_y' => $point->marker_y,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }
};
