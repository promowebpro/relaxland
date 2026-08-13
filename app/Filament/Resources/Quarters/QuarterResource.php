<?php

namespace App\Filament\Resources\Quarters;

use App\Domain\Genplan\GenplanMode;
use App\Domain\Genplan\Quarter;
use App\Domain\Genplan\QuarterStatus;
use App\Domain\Genplan\Rules\NormalizedCoordinate;
use App\Domain\Genplan\Rules\NormalizedPolygon;
use App\Filament\Resources\Quarters\Pages\CreateQuarter;
use App\Filament\Resources\Quarters\Pages\EditQuarter;
use App\Filament\Resources\Quarters\Pages\ListQuarters;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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

class QuarterResource extends Resource
{
    protected static ?string $model = Quarter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static string|UnitEnum|null $navigationGroup = 'Посёлок';

    protected static ?string $navigationLabel = 'Кварталы';

    protected static ?string $modelLabel = 'квартал';

    protected static ?string $pluralModelLabel = 'кварталы';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        $coordinate = fn (string $name, string $label) => TextInput::make($name)->label($label)->numeric()->step(0.000001)->minValue(0)->maxValue(1)->rule(new NormalizedCoordinate(true));

        return $schema->components([
            Section::make('Квартал')->columns(2)->schema([
                Select::make('genplan_id')->label('Генплан')->relationship('genplan', 'name')->required()->searchable()->preload(),
                TextInput::make('name')->label('Название')->required()->maxLength(255),
                TextInput::make('slug')->label('Slug')->required()->alphaDash()->maxLength(255)->unique(modifyRuleUsing: fn ($rule, $get) => $rule->where('genplan_id', $get('genplan_id')), ignoreRecord: true),
                Select::make('status')->label('Статус')->options(self::enumOptions())->required(),
                Textarea::make('description')->label('Описание')->rows(4)->maxLength(3000)->columnSpanFull(),
                TextInput::make('sort_order')->label('Порядок')->numeric()->integer()->minValue(0)->default(0)->required(),
                Toggle::make('is_active')->label('Активен')->default(false),
            ]),
            self::geometrySection(GenplanMode::TwoD, $coordinate),
            self::geometrySection(GenplanMode::ThreeD, $coordinate),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            TextColumn::make('genplan.name')->label('Генплан')->sortable(), TextColumn::make('name')->label('Квартал')->searchable(),
            TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn (QuarterStatus $state) => $state->label()),
            TextColumn::make('plots_count')->label('Участки')->counts('plots'), IconColumn::make('is_active')->label('Активен')->boolean(),
        ])->filters([SelectFilter::make('genplan_id')->label('Генплан')->relationship('genplan', 'name'), SelectFilter::make('status')->label('Статус')->options(self::enumOptions())])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([]);
    }

    private static function enumOptions(): array
    {
        return collect(QuarterStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all();
    }

    private static function geometrySection(GenplanMode $mode, callable $coordinate): Section
    {
        $prefix = "geometry_{$mode->value}";

        return Section::make("Геометрия {$mode->label()}")
            ->description("Координаты 0..1 относительно отдельного canvas {$mode->label()}; geometry другого режима не подставляется.")
            ->columns(2)
            ->schema([
                $coordinate("{$prefix}_label_x", 'Label X'),
                $coordinate("{$prefix}_label_y", 'Label Y'),
                Repeater::make("{$prefix}_polygon_data")->label("Полигон {$mode->label()}")->rule(new NormalizedPolygon(true))->columns(2)->schema([
                    $coordinate('x', 'X')->required(), $coordinate('y', 'Y')->required(),
                ])->addActionLabel('Добавить точку')->reorderable()->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListQuarters::route('/'), 'create' => CreateQuarter::route('/create'), 'edit' => EditQuarter::route('/{record}/edit')];
    }
}
