<?php

namespace App\Filament\Resources\Plots;

use App\Domain\Genplan\Plot;
use App\Domain\Genplan\PlotStatus;
use App\Domain\Genplan\Rules\NormalizedCoordinate;
use App\Filament\Resources\Plots\Pages\CreatePlot;
use App\Filament\Resources\Plots\Pages\EditPlot;
use App\Filament\Resources\Plots\Pages\ListPlots;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
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

class PlotResource extends Resource
{
    protected static ?string $model = Plot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Посёлок';

    protected static ?string $navigationLabel = 'Участки';

    protected static ?string $modelLabel = 'участок';

    protected static ?string $pluralModelLabel = 'участки';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        $coordinate = fn (string $name, string $label) => TextInput::make($name)->label($label)->numeric()->step(0.000001)->minValue(0)->maxValue(1)->rule(new NormalizedCoordinate(true));

        return $schema->components([
            Section::make('Участок')->columns(2)->schema([
                Select::make('quarter_id')->label('Квартал')->relationship('quarter', 'name')->required()->searchable()->preload(),
                TextInput::make('number')->label('Номер')->required()->maxLength(64),
                TextInput::make('slug')->label('Slug')->required()->alphaDash()->maxLength(255)->unique(modifyRuleUsing: fn ($rule, $get) => $rule->where('quarter_id', $get('quarter_id')), ignoreRecord: true),
                Select::make('status')->label('Статус')->options(self::enumOptions())->required(),
                TextInput::make('area')->label('Площадь, сотки')->numeric()->step(0.01)->minValue(0.01)->required(),
                TextInput::make('price')->label('Цена')->numeric()->step(0.01)->minValue(0),
                TextInput::make('price_per_sotka')->label('Цена за сотку')->numeric()->step(0.01)->minValue(0),
                Toggle::make('is_visible')->label('Показывать публично')->default(false),
                Textarea::make('description')->label('Описание')->rows(4)->maxLength(5000)->columnSpanFull(),
                FileUpload::make('image')->label('Изображение')->disk('public')->directory('genplan/plots')->image()->maxSize(15360),
                $coordinate('marker_x', 'Marker X'), $coordinate('marker_y', 'Marker Y'),
                KeyValue::make('attributes')->label('Атрибуты')->keyLabel('Название')->valueLabel('Значение')->columnSpanFull(),
            ]),
            Section::make('Полигон участка')->description('Необязательный foundation до Release 7. Минимум три точки.')->schema([
                Repeater::make('polygon_data')->label('Точки')->minItems(3)->columns(2)->schema([
                    $coordinate('x', 'X')->required(), $coordinate('y', 'Y')->required(),
                ])->addActionLabel('Добавить точку')->reorderable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')->columns([
            TextColumn::make('number')->label('Номер')->searchable(), TextColumn::make('quarter.name')->label('Квартал')->sortable(),
            TextColumn::make('area')->label('Площадь')->suffix(' сот.'), TextColumn::make('price')->label('Цена')->money('RUB')->toggleable(),
            TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn (PlotStatus $state) => $state->label()), IconColumn::make('is_visible')->label('Виден')->boolean(),
        ])->filters([SelectFilter::make('quarter_id')->label('Квартал')->relationship('quarter', 'name'), SelectFilter::make('status')->label('Статус')->options(self::enumOptions())])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([]);
    }

    private static function enumOptions(): array
    {
        return collect(PlotStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all();
    }

    public static function getPages(): array
    {
        return ['index' => ListPlots::route('/'), 'create' => CreatePlot::route('/create'), 'edit' => EditPlot::route('/{record}/edit')];
    }
}
