<?php

namespace App\Domain\Genplan;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class PlotFilters
{
    public const SORT_DEFAULT = 'default';

    public const SORT_PRICE_ASC = 'price_asc';

    public const SORT_AREA_ASC = 'area_asc';

    public const SORT_AREA_DESC = 'area_desc';

    public function __construct(
        public ?PlotStatus $status = null,
        public ?string $areaMin = null,
        public ?string $areaMax = null,
        public ?string $priceMin = null,
        public ?string $priceMax = null,
        public string $sort = self::SORT_DEFAULT,
    ) {}

    /** @param array<string, mixed> $input */
    public static function fromArray(array $input): self
    {
        $status = is_string($input['status'] ?? null) ? PlotStatus::tryFrom($input['status']) : null;
        if ($status === PlotStatus::Hidden) {
            $status = null;
        }

        $sort = is_string($input['sort'] ?? null) && in_array($input['sort'], self::sorts(), true)
            ? $input['sort']
            : self::SORT_DEFAULT;
        $areaMin = self::decimal($input['area_min'] ?? null);
        $areaMax = self::decimal($input['area_max'] ?? null);
        $priceMin = self::decimal($input['price_min'] ?? null);
        $priceMax = self::decimal($input['price_max'] ?? null);

        if ($areaMin !== null && $areaMax !== null && self::compare($areaMin, $areaMax) > 0) {
            $areaMin = $areaMax = null;
        }

        if ($priceMin !== null && $priceMax !== null && self::compare($priceMin, $priceMax) > 0) {
            $priceMin = $priceMax = null;
        }

        return new self($status, $areaMin, $areaMax, $priceMin, $priceMax, $sort);
    }

    /** @return list<string> */
    public static function sorts(): array
    {
        return [self::SORT_DEFAULT, self::SORT_PRICE_ASC, self::SORT_AREA_ASC, self::SORT_AREA_DESC];
    }

    /** @return list<string> */
    public static function publicStatuses(): array
    {
        return [PlotStatus::Available->value, PlotStatus::Reserved->value, PlotStatus::Sold->value];
    }

    public function apply(Builder $query): Builder
    {
        $query
            ->when($this->status, fn (Builder $builder, PlotStatus $status) => $builder->where('status', $status->value))
            ->when($this->areaMin, fn (Builder $builder, string $value) => $builder->where('area', '>=', $value))
            ->when($this->areaMax, fn (Builder $builder, string $value) => $builder->where('area', '<=', $value))
            ->when($this->priceMin, fn (Builder $builder, string $value) => $builder->whereNotNull('price')->where('price', '>=', $value))
            ->when($this->priceMax, fn (Builder $builder, string $value) => $builder->whereNotNull('price')->where('price', '<=', $value));

        return match ($this->sort) {
            self::SORT_PRICE_ASC => $query->orderByRaw('CASE WHEN price IS NULL THEN 1 ELSE 0 END')->orderBy('price')->orderBy('number')->orderBy('id'),
            self::SORT_AREA_ASC => $query->orderBy('area')->orderBy('number')->orderBy('id'),
            self::SORT_AREA_DESC => $query->orderByDesc('area')->orderBy('number')->orderBy('id'),
            default => $query->orderBy('number')->orderBy('id'),
        };
    }

    /**
     * @param  Collection<int, Plot>  $plots
     * @return Collection<int, Plot>
     */
    public function applyToCollection(Collection $plots): Collection
    {
        $filtered = $plots->filter(function (Plot $plot): bool {
            if ($this->status && $plot->status !== $this->status) {
                return false;
            }
            if ($this->areaMin !== null && self::compare($plot->area, $this->areaMin) < 0) {
                return false;
            }
            if ($this->areaMax !== null && self::compare($plot->area, $this->areaMax) > 0) {
                return false;
            }
            if (($this->priceMin !== null || $this->priceMax !== null) && $plot->price === null) {
                return false;
            }
            if ($this->priceMin !== null && self::compare($plot->price, $this->priceMin) < 0) {
                return false;
            }

            return $this->priceMax === null || self::compare($plot->price, $this->priceMax) <= 0;
        });

        return (match ($this->sort) {
            self::SORT_PRICE_ASC => $filtered->sort(fn (Plot $a, Plot $b): int => self::compareNullable($a->price, $b->price) ?: strnatcasecmp($a->number, $b->number)),
            self::SORT_AREA_ASC => $filtered->sort(fn (Plot $a, Plot $b): int => self::compare($a->area, $b->area) ?: strnatcasecmp($a->number, $b->number)),
            self::SORT_AREA_DESC => $filtered->sort(fn (Plot $a, Plot $b): int => self::compare($b->area, $a->area) ?: strnatcasecmp($a->number, $b->number)),
            default => $filtered->sort(fn (Plot $a, Plot $b): int => strnatcasecmp($a->number, $b->number) ?: $a->id <=> $b->id),
        })->values();
    }

    /** @return array<string, string> */
    public function query(): array
    {
        return array_filter([
            'status' => $this->status?->value,
            'area_min' => $this->areaMin,
            'area_max' => $this->areaMax,
            'price_min' => $this->priceMin,
            'price_max' => $this->priceMax,
            'sort' => $this->sort === self::SORT_DEFAULT ? null : $this->sort,
        ], fn (?string $value): bool => $value !== null && $value !== '');
    }

    private static function decimal(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            return null;
        }

        $value = trim((string) $value);

        return preg_match('/^\d{1,13}(?:\.\d{1,2})?$/', $value) ? $value : null;
    }

    private static function compare(?string $left, ?string $right): int
    {
        [$leftWhole, $leftFraction] = array_pad(explode('.', (string) $left, 2), 2, '');
        [$rightWhole, $rightFraction] = array_pad(explode('.', (string) $right, 2), 2, '');
        $leftScaled = ltrim($leftWhole.str_pad(substr($leftFraction, 0, 2), 2, '0'), '0') ?: '0';
        $rightScaled = ltrim($rightWhole.str_pad(substr($rightFraction, 0, 2), 2, '0'), '0') ?: '0';

        return strlen($leftScaled) <=> strlen($rightScaled) ?: strcmp($leftScaled, $rightScaled);
    }

    private static function compareNullable(?string $left, ?string $right): int
    {
        if ($left === null) {
            return $right === null ? 0 : 1;
        }

        return $right === null ? -1 : self::compare($left, $right);
    }
}
