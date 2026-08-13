<?php

namespace App\Filament\Resources\Genplans;

use App\Domain\Genplan\Genplan;
use App\Filament\Resources\Genplans\Pages\CreateGenplan;
use App\Filament\Resources\Genplans\Pages\EditGenplan;
use App\Filament\Resources\Genplans\Pages\ListGenplans;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class GenplanResource extends Resource
{
    protected static ?string $model = Genplan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|UnitEnum|null $navigationGroup = 'Посёлок';

    protected static ?string $navigationLabel = 'Генплан';

    protected static ?string $modelLabel = 'генплан';

    protected static ?string $pluralModelLabel = 'генпланы';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        $upload = fn (string $field, string $label) => FileUpload::make($field)->label($label)->disk('public')->directory('genplan/plans')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])->maxSize(20480);

        return $schema->components([
            Section::make('Генплан')->columns(2)->schema([
                TextInput::make('name')->label('Название')->required()->maxLength(255),
                TextInput::make('slug')->label('Slug')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255),
                $upload('image_3d', 'Изображение 3D')->required(),
                $upload('image_2d', 'Изображение 2D')->required(),
                $upload('mobile_image_3d', 'Мобильное изображение 3D'),
                $upload('mobile_image_2d', 'Мобильное изображение 2D'),
                TextInput::make('original_width')->label('Исходная ширина')->numeric()->integer()->minValue(1),
                TextInput::make('original_height')->label('Исходная высота')->numeric()->integer()->minValue(1),
                Toggle::make('is_active')->label('Активен')->default(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id')->columns([
            TextColumn::make('name')->label('Название')->searchable(),
            TextColumn::make('slug')->label('Slug')->searchable(),
            TextColumn::make('quarters_count')->label('Кварталы')->counts('quarters'),
            IconColumn::make('is_active')->label('Активен')->boolean(),
            TextColumn::make('updated_at')->label('Изменён')->dateTime()->sortable(),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => ListGenplans::route('/'), 'create' => CreateGenplan::route('/create'), 'edit' => EditGenplan::route('/{record}/edit')];
    }
}
