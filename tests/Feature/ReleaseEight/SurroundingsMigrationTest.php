<?php

namespace Tests\Feature\ReleaseEight;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SurroundingsMigrationTest extends TestCase
{
    public function test_release_eight_backfills_public_slug_and_genplan_relation_and_rolls_back(): void
    {
        $database = storage_path('app/release8-migration-test.sqlite');
        $originalDefault = Config::get('database.default');
        $originalConnection = Config::get('database.connections.release8_test');

        $this->assertFileDoesNotExist($database);
        touch($database);

        try {
            Config::set('database.connections.release8_test', [
                'driver' => 'sqlite',
                'database' => $database,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
            Config::set('database.default', 'release8_test');
            DB::purge('release8_test');

            $this->migrateFile('2026_08_13_150000_create_genplan_foundation_tables.php');
            $now = now();
            $genplanId = DB::connection('release8_test')->table('genplans')->insertGetId([
                'name' => 'Public', 'slug' => 'public', 'image_3d' => '3d.webp', 'image_2d' => '2d.webp',
                'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::connection('release8_test')->table('surrounding_places')->insert([
                'name' => 'Можайское море', 'category' => 'entertainment', 'latitude' => '55.5',
                'longitude' => '36.1', 'sort_order' => 0, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);

            $this->migrateFile('2026_08_17_120000_add_public_map_contract_to_surrounding_places.php');

            $record = DB::connection('release8_test')->table('surrounding_places')->sole();
            $this->assertSame($genplanId, $record->genplan_id);
            $this->assertSame('mozaiskoe-more', $record->slug);
            $this->assertTrue(Schema::connection('release8_test')->hasColumn('surrounding_places', 'image'));

            Artisan::call('migrate:rollback', [
                '--database' => 'release8_test',
                '--path' => $this->migrationPath('2026_08_17_120000_add_public_map_contract_to_surrounding_places.php'),
                '--realpath' => true,
                '--force' => true,
            ]);

            $this->assertFalse(Schema::connection('release8_test')->hasColumn('surrounding_places', 'slug'));
            $this->assertFalse(Schema::connection('release8_test')->hasColumn('surrounding_places', 'genplan_id'));
            $this->assertFalse(Schema::connection('release8_test')->hasColumn('surrounding_places', 'image'));
        } finally {
            DB::purge('release8_test');
            Config::set('database.default', $originalDefault);
            Config::set('database.connections.release8_test', $originalConnection);
            if (is_file($database)) {
                unlink($database);
            }
        }
    }

    private function migrateFile(string $file): void
    {
        $exit = Artisan::call('migrate', [
            '--database' => 'release8_test',
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
}
