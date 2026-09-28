<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'slug',
        'excerpt',
        'short_phrase',
        'image',
        'image_alt',
        'video',
        'video_duration',
        'audio',
        'audio_duration',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'video_duration' => 'integer',
            'audio_duration' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function imageUrl(): ?string
    {
        return $this->publicUrl($this->image);
    }

    public function videoUrl(): ?string
    {
        return $this->publicUrl($this->video);
    }

    public function audioUrl(): ?string
    {
        return $this->publicUrl($this->audio);
    }

    public function formattedAudioDuration(): ?string
    {
        return $this->formatDuration($this->audio_duration);
    }

    public function formattedVideoDuration(): ?string
    {
        return $this->formatDuration($this->video_duration);
    }

    public function hasMedia(): bool
    {
        return filled($this->audio) || filled($this->video);
    }

    private function publicUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    private function formatDuration(?int $seconds): ?string
    {
        if ($seconds === null || $seconds < 0) {
            return null;
        }

        $minutes = intdiv($seconds, 60);
        $rest = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $rest);
    }
}
