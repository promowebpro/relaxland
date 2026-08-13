<?php

namespace App\Filament\Resources\SurroundingPlaces;

use App\Domain\Genplan\SurroundingCategory;
use App\Domain\Genplan\SurroundingPlace;
use App\Filament\Resources\SurroundingPlaces\Pages\CreateSurroundingPlace;
use App\Filament\Resources\SurroundingPlaces\Pages\EditSurroundingPlace;
use App\Filament\Resources\SurroundingPlaces\Pages\ListSurroundingPlaces;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SurroundingPlaceResource extends Resource
{
    protected static ?string $model = SurroundingPlace::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeEuropeAfrica;

    protected static string|UnitEnum|null $navigationGroup = 'Посёлок';

    protected static ?string $navigationLabel = 'Окружение';

    protected static ?string $modelLabel = 'место окружения';

    protected static ?string $pluralModelLabel = 'окружение';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Место')->columns(2)->schema([
            TextInput::make('name')->label('Название')->required()->maxLength(255),
            Select::make('category')->label('Категория')->options(self::enumOptions())->required(),
            TextInput::make('latitude')->label('Широта')->numeric()->step(0.0000001)->minValue(-90)->maxValue(90)->required(),
            TextInput::make('longitude')->label('Долгота')->numeric()->step(0.0000001)->minValue(-180)->maxValue(180)->required(),
            Textarea::make('description')->label('Описание')->rows(4)->maxLength(3000)->columnSpanFull(),
            TextInput::make('external_url')->label('Внешняя ссылка')->url()->maxLength(2048)->columnSpanFull(),
            TextInput::make('sort_order')->label('Порядок')->numeric()->integer()->minValue(0)->default(0)->required(),
            Toggle::make('is_active')->label('Активно')->default(false),
        ])]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            TextColumn::make('name')->label('Название')->searchable(), TextColumn::make('category')->label('Категория')->formatStateUsing(fn (SurroundingCategory $state) => $state->label()),
            TextColumn::make('latitude')->label('Широта'), TextColumn::make('longitude')->label('Долгота'), IconColumn::make('is_active')->label('Активно')->boolean(),
        ])->filters([SelectFilter::make('category')->label('Категория')->options(self::enumOptions())])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([]);
    }

    private static function enumOptions(): array
    {
        return collect(SurroundingCategory::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all();
    }

    public static function getPages(): array
    {
        return ['index' => ListSurroundingPlaces::route('/'), 'create' => CreateSurroundingPlace::route('/create'), 'edit' => EditSurroundingPlace::route('/{record}/edit')];
    }
}
