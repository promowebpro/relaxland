<?php

namespace Tests\Feature\ReleaseFive;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GenplanMultiViewMigrationTest extends TestCase
{
    public function test_legacy_geometry_moves_only_to_three_d_and_rollback_restores_it(): void
    {
        $database = storage_path('app/release5a-migration-test.sqlite');
        $originalDefault = Config::get('database.default');
        $originalConnection = Config::get('database.connections.release5a_test');

        $this->assertFileDoesNotExist($database);
        touch($database);

        try {
            Config::set('database.connections.release5a_test', [
                'driver' => 'sqlite',
                'database' => $database,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
            Config::set('database.default', 'release5a_test');
            DB::purge('release5a_test');

            $this->migrateFile('2026_08_13_150000_create_genplan_foundation_tables.php');
            $now = now();
            $genplanId = DB::table('genplans')->insertGetId([
                'name' => 'Legacy', 'slug' => 'legacy', 'image_3d' => '3d.webp', 'image_2d' => '2d.webp',
                'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $quarterId = DB::table('quarters')->insertGetId([
                'genplan_id' => $genplanId, 'name' => 'Quarter', 'slug' => 'quarter', 'status' => 'available',
                'polygon_data' => json_encode($this->polygon()), 'label_x' => 0.25, 'label_y' => 0.25,
                'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $plotId = DB::table('plots')->insertGetId([
                'quarter_id' => $quarterId, 'number' => '1', 'slug' => 'plot-1', 'area' => 10,
                'status' => 'available', 'marker_x' => 0.3, 'marker_y' => 0.4, 'is_visible' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $pointId = DB::table('infrastructure_points')->insertGetId([
                'genplan_id' => $genplanId, 'name' => 'Point', 'category' => 'sport',
                'marker_x' => 0.6, 'marker_y' => 0.7, 'show_on_3d' => true, 'show_on_2d' => true,
                'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);

            $this->migrateFile('2026_08_13_160000_create_view_specific_genplan_geometries.php');

            $this->assertDatabaseHas('quarter_geometries', ['quarter_id' => $quarterId, 'mode' => '3d'], 'release5a_test');
            $this->assertDatabaseMissing('quarter_geometries', ['quarter_id' => $quarterId, 'mode' => '2d'], 'release5a_test');
            $this->assertDatabaseHas('plot_geometries', ['plot_id' => $plotId, 'mode' => '3d'], 'release5a_test');
            $this->assertDatabaseHas('infrastructure_point_geometries', ['infrastructure_point_id' => $pointId, 'mode' => '3d'], 'release5a_test');
            $this->assertFalse(Schema::connection('release5a_test')->hasColumn('quarters', 'polygon_data'));

            Artisan::call('migrate:rollback', [
                '--database' => 'release5a_test',
                '--path' => $this->migrationPath('2026_08_13_160000_create_view_specific_genplan_geometries.php'),
                '--realpath' => true,
                '--force' => true,
            ]);

            $this->assertTrue(Schema::connection('release5a_test')->hasColumn('quarters', 'polygon_data'));
            $legacyQuarter = DB::connection('release5a_test')->table('quarters')->find($quarterId);
            $legacyPoint = DB::connection('release5a_test')->table('infrastructure_points')->find($pointId);
            $this->assertSame(0.25, (float) $legacyQuarter->label_x);
            $this->assertSame(0.6, (float) $legacyPoint->marker_x);
        } finally {
            DB::purge('release5a_test');
            Config::set('database.default', $originalDefault);
            Config::set('database.connections.release5a_test', $originalConnection);
            if (is_file($database)) {
                unlink($database);
            }
        }
    }

    private function migrateFile(string $file): void
    {
        $exit = Artisan::call('migrate', [
            '--database' => 'release5a_test',
            '--path' => $this->migrationPath($file),
            '--realpath' => true,
            '--force' => true,
        ]);

        $this->assertSame(0, $exit, Artisan::output());
    }

    private function migrationPath(string $file): string
    {
        return database_path("migrations/{$file}");
    }

    private function polygon(): array
    {
        return [
            ['x' => 0.1, 'y' => 0.1],
            ['x' => 0.4, 'y' => 0.1],
            ['x' => 0.4, 'y' => 0.4],
        ];
    }
}
