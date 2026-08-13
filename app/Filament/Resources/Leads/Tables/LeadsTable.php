<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Domain\Leads\LeadFormType;
use App\Domain\Leads\LeadSource;
use App\Domain\Leads\LeadStatus;
use App\Domain\Users\Enums\PermissionName;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Создана')->dateTime()->sortable(),
                TextColumn::make('name')->label('Имя')->placeholder('—')->searchable()->limit(40),
                TextColumn::make('phone')->label('Телефон')->searchable()->copyable(),
                TextColumn::make('email')->label('Email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('source')->label('Источник')->badge()->formatStateUsing(fn (LeadSource $state): string => $state->label()),
                TextColumn::make('form_type')->label('Форма')->formatStateUsing(fn (LeadFormType $state): string => $state->label()),
                TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn (LeadStatus $state): string => $state->label()),
                TextColumn::make('assignedUser.name')->label('Ответственный')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Статус')->options(self::enumOptions(LeadStatus::cases())),
                SelectFilter::make('source')->label('Источник')->options(self::enumOptions(LeadSource::cases())),
                SelectFilter::make('form_type')->label('Тип формы')->options(self::enumOptions(LeadFormType::cases())),
                SelectFilter::make('assigned_to')->label('Ответственный')->relationship('assignedUser', 'name'),
                Filter::make('created_at')
                    ->label('Дата создания')
                    ->schema([
                        DatePicker::make('created_from')->label('С даты'),
                        DatePicker::make('created_until')->label('По дату'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['created_from'] ?? null),
                            fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $data['created_from']),
                        )
                        ->when(
                            filled($data['created_until'] ?? null),
                            fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $data['created_until']),
                        )),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->visible(fn (): bool => auth()->user()?->can(PermissionName::LeadsUpdate->value) ?? false),
            ])
            ->toolbarActions([]);
    }

    /** @param array<int, LeadStatus|LeadSource|LeadFormType> $cases */
    private static function enumOptions(array $cases): array
    {
        return collect($cases)->mapWithKeys(fn ($case): array => [$case->value => $case->label()])->all();
    }
}
