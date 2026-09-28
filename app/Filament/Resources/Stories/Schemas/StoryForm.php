<?php

namespace App\Filament\Resources\Stories\Schemas;

use App\Domain\Users\Enums\PermissionName;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoryForm
{
    public static function configure(Schema $schema): Schema
    {
        $canPublish = fn (): bool => auth()->user()?->can(PermissionName::ContentPublish->value) ?? false;

        return $schema->components([
            Section::make('История')
                ->columns(2)
                ->schema([
                    TextInput::make('title')->label('Имя / заголовок')->required()->maxLength(255),
                    TextInput::make('subtitle')->label('Подпись')->maxLength(255),
                    TextInput::make('slug')->label('Slug')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255),
                    TextInput::make('short_phrase')->label('Эмоциональный тезис')->maxLength(255)->helperText('Показывается в облаке рядом с карточкой'),
                    Textarea::make('excerpt')->label('Цитата / текст')->rows(4)->maxLength(2000)->columnSpanFull(),
                    FileUpload::make('image')->label('Обложка / фото')->disk('public')->directory('stories/images')->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])->maxSize(15360)->image(),
                    TextInput::make('image_alt')->label('Alt обложки')->maxLength(500),
                    FileUpload::make('audio')->label('Аудио')->disk('public')->directory('stories/audio')->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'audio/webm', 'audio/mp4', 'audio/aac'])->maxSize(40960),
                    TextInput::make('audio_duration')->label('Длительность аудио, сек')->numeric()->minValue(0)->helperText('Необязательно — на фронте подтянется из файла'),
                    FileUpload::make('video')->label('Видео')->disk('public')->directory('stories/video')->acceptedFileTypes(['video/mp4', 'video/webm'])->maxSize(102400),
                    TextInput::make('video_duration')->label('Длительность видео, сек')->numeric()->minValue(0),
                    TextInput::make('sort_order')->label('Порядок')->numeric()->minValue(0)->default(0)->required(),
                    Toggle::make('is_active')->label('Опубликована')->default(false)->disabled(fn (): bool => ! $canPublish()),
                ]),
        ]);
    }
}
