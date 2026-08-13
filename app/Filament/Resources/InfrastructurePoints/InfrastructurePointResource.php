<?php

namespace App\Filament\Resources\InfrastructurePoints;

use App\Domain\Genplan\GenplanMode;
use App\Domain\Genplan\InfrastructureCategory;
use App\Domain\Genplan\InfrastructurePoint;
use App\Domain\Genplan\Rules\NormalizedCoordinate;
use App\Filament\Resources\InfrastructurePoints\Pages\CreateInfrastructurePoint;
use App\Filament\Resources\InfrastructurePoints\Pages\EditInfrastructurePoint;
use App\Filament\Resources\InfrastructurePoints\Pages\ListInfrastructurePoints;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
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

class InfrastructurePointResource extends Resource
{
    protected static ?string $model = InfrastructurePoint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Посёлок';

    protected static ?string $navigationLabel = 'Инфраструктура';

    protected static ?string $modelLabel = 'объект инфраструктуры';

    protected static ?string $pluralModelLabel = 'инфраструктура';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        $coordinate = fn (string $name, string $label) => TextInput::make($name)->label($label)->numeric()->step(0.000001)->minValue(0)->maxValue(1)->rule(new NormalizedCoordinate(true));

        return $schema->components([Section::make('Объект')->columns(2)->schema([
            Select::make('genplan_id')->label('Генплан')->relationship('genplan', 'name')->required()->searchable()->preload(),
            TextInput::make('name')->label('Название')->required()->maxLength(255),
            Select::make('category')->label('Категория')->options(self::enumOptions())->required(), TextInput::make('icon')->label('Ключ иконки')->alphaDash()->maxLength(100),
            FileUpload::make('image')->label('Изображение')->disk('public')->directory('genplan/infrastructure')->image()->maxSize(15360),
            Textarea::make('description')->label('Описание')->rows(4)->maxLength(3000),
            Toggle::make('show_on_3d')->label('Показывать в 3D')->default(true), Toggle::make('show_on_2d')->label('Показывать в 2D')->default(true),
            TextInput::make('sort_order')->label('Порядок')->numeric()->integer()->minValue(0)->default(0)->required(), Toggle::make('is_active')->label('Активен')->default(false),
        ]), self::geometrySection(GenplanMode::TwoD, $coordinate), self::geometrySection(GenplanMode::ThreeD, $coordinate)]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            TextColumn::make('name')->label('Название')->searchable(), TextColumn::make('genplan.name')->label('Генплан'),
            TextColumn::make('category')->label('Категория')->formatStateUsing(fn (InfrastructureCategory $state) => $state->label()),
            IconColumn::make('show_on_3d')->label('3D')->boolean(), IconColumn::make('show_on_2d')->label('2D')->boolean(), IconColumn::make('is_active')->label('Активен')->boolean(),
        ])->filters([SelectFilter::make('category')->label('Категория')->options(self::enumOptions())])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([]);
    }

    private static function enumOptions(): array
    {
        return collect(InfrastructureCategory::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all();
    }

    private static function geometrySection(GenplanMode $mode, callable $coordinate): Section
    {
        $prefix = "geometry_{$mode->value}";

        return Section::make("Позиция {$mode->label()}")
            ->description("Маркер относится только к projection space {$mode->label()}; visibility flag без координат маркер не создаёт.")
            ->columns(2)
            ->schema([
                $coordinate("{$prefix}_marker_x", 'Marker X')->requiredWith("{$prefix}_marker_y"),
                $coordinate("{$prefix}_marker_y", 'Marker Y')->requiredWith("{$prefix}_marker_x"),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListInfrastructurePoints::route('/'), 'create' => CreateInfrastructurePoint::route('/create'), 'edit' => EditInfrastructurePoint::route('/{record}/edit')];
    }
}
