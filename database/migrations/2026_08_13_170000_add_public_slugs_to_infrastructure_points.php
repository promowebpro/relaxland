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
        Schema::table('infrastructure_points', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $used = [];

        DB::table('infrastructure_points')
            ->select(['id', 'genplan_id', 'name'])
            ->orderBy('genplan_id')
            ->orderBy('id')
            ->each(function ($point) use (&$used): void {
                $base = Str::slug($point->name) ?: 'point';
                $key = (string) $point->genplan_id;
                $candidate = $base;

                if (isset($used[$key][$candidate])) {
                    $candidate = "{$base}-{$point->id}";
                }

                while (isset($used[$key][$candidate])) {
                    $candidate .= '-point';
                }

                $used[$key][$candidate] = true;
                DB::table('infrastructure_points')->where('id', $point->id)->update(['slug' => $candidate]);
            });

        Schema::table('infrastructure_points', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique(['genplan_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('infrastructure_points', function (Blueprint $table) {
            $table->dropUnique(['genplan_id', 'slug']);
            $table->dropColumn('slug');
        });
    }
};
