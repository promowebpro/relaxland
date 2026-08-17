<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surrounding_places', function (Blueprint $table): void {
            $table->unsignedBigInteger('genplan_id')->nullable()->after('id');
            $table->string('slug')->nullable()->after('name');
            $table->string('image')->nullable()->after('description');
        });

        $fallbackGenplanId = DB::table('genplans')
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id') ?? DB::table('genplans')->orderBy('id')->value('id');

        $places = DB::table('surrounding_places')->orderBy('id')->get(['id', 'name']);

        if ($places->isNotEmpty() && $fallbackGenplanId === null) {
            throw new RuntimeException('SurroundingPlace records require an existing Genplan before Release 8 migration.');
        }

        foreach ($places as $place) {
            $base = Str::slug((string) $place->name) ?: 'place-'.$place->id;
            $slug = $base;
            $suffix = 2;

            while (DB::table('surrounding_places')
                ->where('genplan_id', $fallbackGenplanId)
                ->where('slug', $slug)
                ->exists()) {
                $slug = $base.'-'.$suffix++;
            }

            DB::table('surrounding_places')->where('id', $place->id)->update([
                'genplan_id' => $fallbackGenplanId,
                'slug' => $slug,
            ]);
        }

        Schema::table('surrounding_places', function (Blueprint $table): void {
            $table->unsignedBigInteger('genplan_id')->nullable(false)->change();
            $table->string('slug')->nullable(false)->change();
            $table->foreign('genplan_id')->references('id')->on('genplans')->restrictOnDelete();
            $table->unique(['genplan_id', 'slug']);
            $table->index(['genplan_id', 'is_active', 'sort_order'], 'surrounding_places_public_index');
        });
    }

    public function down(): void
    {
        Schema::table('surrounding_places', function (Blueprint $table): void {
            $table->dropIndex('surrounding_places_public_index');
            $table->dropUnique(['genplan_id', 'slug']);
            $table->dropForeign(['genplan_id']);
            $table->dropColumn(['genplan_id', 'slug', 'image']);
        });
    }
};
