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

    /**
     * Design-mock stories used on the public home when CMS has no active rows.
     *
     * @return \Illuminate\Support\Collection<int, static>
     */
    public static function designDefaults()
    {
        return collect([
            [
                'title' => 'Наталья',
                'subtitle' => 'Бухгалтер с перманентным выгоранием',
                'slug' => 'design-natalya',
                'excerpt' => 'Уже купили участок. Предвкушаем чистый воздух, птиц по утрам и сон, как в детстве у бабушки в деревне.',
                'short_phrase' => "Бодрость\nпосле крепкого сна",
                'image' => 'assets/design/home-target-story-woman-dark.webp',
                'image_alt' => 'Наталья',
                'sort_order' => 10,
            ],
            [
                'title' => 'Иван',
                'subtitle' => 'Любящий сын',
                'slug' => 'design-ivan',
                'excerpt' => 'Покупал участок этого застройщика в другом посёлке, поэтому спокоен за родителей, которые заключают сделку с проверенным девелопером по моей рекомендации.',
                'short_phrase' => "Спокойствие\nза своих близких",
                'image' => 'assets/design/home-target-story-cafe.webp',
                'image_alt' => 'Иван',
                'sort_order' => 20,
            ],
            [
                'title' => 'Анна',
                'subtitle' => 'Анна, Никита и корги с большими планами на дизайнерскую мебель',
                'slug' => 'design-anna',
                'excerpt' => null,
                'short_phrase' => "Чувство\nумиротворения\nи тишина вокруг",
                'image' => 'assets/design/home-target-story-dog.webp',
                'image_alt' => 'Анна с корги',
                'sort_order' => 30,
            ],
            [
                'title' => 'Светлана',
                'subtitle' => 'Люблю путешествовать, заниматься спортом и не люблю стройку',
                'slug' => 'design-svetlana',
                'excerpt' => 'У меня нет ни времени, ни желания заниматься строительством, а потом уходом за домом. К счастью, я нашла девелопера, который предоставляет полноценную службу заботы 24/7 и услугу «Сопровождение стройки». Продано.',
                'short_phrase' => "Безопасность\nи расслабленность",
                'image' => 'assets/design/home-target-story-woman.webp',
                'image_alt' => 'Светлана',
                'sort_order' => 40,
            ],
            [
                'title' => 'Алиса',
                'subtitle' => 'Счастливая мама трёх богатырей',
                'slug' => 'design-alisa',
                'excerpt' => 'Мне нравится, что можно погулять с собакой и детьми в лесу, но быть спокойной за свою семью',
                'short_phrase' => "Свежесть воздуха\nна природе",
                'image' => 'assets/design/home-target-story-woman-blonde.webp',
                'image_alt' => 'Алиса',
                'sort_order' => 50,
            ],
            [
                'title' => 'Артемий',
                'subtitle' => 'Предприниматель, а не сантехник или гувернантка в третьем поколении',
                'slug' => 'design-artemiy',
                'excerpt' => null,
                'short_phrase' => "Радость, что живешь\nлучшую жизнь",
                'image' => 'assets/design/home-target-story-man-library.webp',
                'image_alt' => 'Артемий',
                'sort_order' => 60,
            ],
        ])->map(fn (array $attributes): self => new self(array_merge($attributes, [
            'is_active' => true,
        ])));
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
        if (! filled($path) || str_contains($path, '..')) {
            return null;
        }

        if (str_starts_with($path, 'assets/')) {
            return asset($path);
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
