<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table): void {
            $table->string('subtitle')->nullable()->after('title');
            $table->string('short_phrase')->nullable()->after('excerpt');
            $table->string('audio')->nullable()->after('video');
            $table->unsignedInteger('audio_duration')->nullable()->after('audio');
            $table->unsignedInteger('video_duration')->nullable()->after('video');
        });
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table): void {
            $table->dropColumn(['subtitle', 'short_phrase', 'audio', 'audio_duration', 'video_duration']);
        });
    }
};
