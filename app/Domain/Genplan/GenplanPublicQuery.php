<?php

namespace App\Domain\Genplan;

use Illuminate\Database\Eloquent\Collection;

class GenplanPublicQuery
{
    public function current(): ?Genplan
    {
        return Genplan::query()
            ->select([
                'id', 'name', 'slug', 'image_3d', 'image_2d', 'mobile_image_3d', 'mobile_image_2d',
                'mobile_image_3d_is_compatible', 'mobile_image_2d_is_compatible',
                'original_width', 'original_height', 'is_active',
            ])
            ->active()
            ->orderBy('id')
            ->first();
    }

    public function overview(GenplanMode $mode): ?Genplan
    {
        $genplan = $this->current();

        if (! $genplan) {
            return null;
        }

        $genplan->setRelation('quarters', $this->quarters($genplan, $mode));
        $genplan->setAttribute('selected_mode', $mode);

        return $genplan;
    }

    public function overviewForAllModes(): ?Genplan
    {
        $genplan = $this->current();

        if (! $genplan) {
            return null;
        }

        $genplan->setRelation('quarters', $this->quartersForAllModes($genplan));

        return $genplan;
    }

    /** @return Collection<int, Quarter> */
    public function quarters(Genplan $genplan, GenplanMode $mode): Collection
    {
        return $this->quarterBaseQuery($genplan)
            ->with(['geometries' => fn ($query) => $query->where('mode', $mode->value)])
            ->get();
    }

    /** @return Collection<int, Quarter> */
    public function quartersForAllModes(Genplan $genplan): Collection
    {
        return $this->quarterBaseQuery($genplan)
            ->with(['geometries' => fn ($query) => $query->whereIn('mode', array_column(GenplanMode::cases(), 'value'))])
            ->get();
    }

    public function quarter(string $slug, GenplanMode $mode): ?Quarter
    {
        $genplan = $this->current();

        if (! $genplan) {
            return null;
        }

        return $this->quarterBaseQuery($genplan)
            ->where('slug', $slug)
            ->with(['geometries' => fn ($query) => $query->where('mode', $mode->value)])
            ->first();
    }

    /** @return Collection<int, Plot> */
    public function plots(Quarter $quarter, GenplanMode $mode): Collection
    {
        return Plot::query()
            ->select([
                'id', 'quarter_id', 'number', 'slug', 'area', 'price', 'price_per_sotka',
                'status', 'description', 'image', 'is_visible',
            ])
            ->whereBelongsTo($quarter)
            ->publiclyVisible()
            ->with(['geometries' => fn ($query) => $query->where('mode', $mode->value)])
            ->orderBy('number')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, InfrastructurePoint> */
    public function infrastructure(GenplanMode $mode): Collection
    {
        $genplan = $this->current();

        if (! $genplan) {
            return new Collection;
        }

        $visibilityField = $mode === GenplanMode::TwoD ? 'show_on_2d' : 'show_on_3d';

        return $this->infrastructureBaseQuery($genplan)
            ->where($visibilityField, true)
            ->whereHas('geometries', fn ($query) => $query->where('mode', $mode->value))
            ->with(['geometries' => fn ($query) => $query->where('mode', $mode->value)])
            ->get();
    }

    /** @return Collection<int, InfrastructurePoint> */
    public function infrastructureForAllModes(): Collection
    {
        $genplan = $this->current();

        if (! $genplan) {
            return new Collection;
        }

        return $this->infrastructureBaseQuery($genplan)
            ->where(function ($query): void {
                $query->where('show_on_2d', true)->orWhere('show_on_3d', true);
            })
            ->whereHas('geometries', fn ($query) => $query->whereIn('mode', array_column(GenplanMode::cases(), 'value')))
            ->with(['geometries' => fn ($query) => $query->whereIn('mode', array_column(GenplanMode::cases(), 'value'))])
            ->get();
    }

    /** @return Collection<int, SurroundingPlace> */
    public function surroundings(): Collection
    {
        return SurroundingPlace::query()
            ->select(['id', 'name', 'category', 'latitude', 'longitude', 'description', 'external_url', 'sort_order', 'is_active'])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function quarterBaseQuery(Genplan $genplan)
    {
        return Quarter::query()
            ->select(['id', 'genplan_id', 'name', 'slug', 'description', 'status', 'sort_order', 'is_active'])
            ->whereBelongsTo($genplan)
            ->publiclyVisible()
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    private function infrastructureBaseQuery(Genplan $genplan)
    {
        return InfrastructurePoint::query()
            ->select([
                'id', 'genplan_id', 'name', 'slug', 'category', 'icon', 'image', 'description',
                'show_on_3d', 'show_on_2d', 'sort_order', 'is_active',
            ])
            ->whereBelongsTo($genplan)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
