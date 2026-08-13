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
                'original_width', 'original_height', 'is_active',
            ])
            ->active()
            ->orderBy('id')
            ->first();
    }

    public function overview(): ?Genplan
    {
        $genplan = $this->current();

        if (! $genplan) {
            return null;
        }

        $genplan->setRelation('quarters', $this->quarters($genplan));

        return $genplan;
    }

    /** @return Collection<int, Quarter> */
    public function quarters(Genplan $genplan): Collection
    {
        return Quarter::query()
            ->select([
                'id', 'genplan_id', 'name', 'slug', 'description', 'status', 'polygon_data',
                'label_x', 'label_y', 'sort_order', 'is_active',
            ])
            ->whereBelongsTo($genplan)
            ->publiclyVisible()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function quarter(string $slug): ?Quarter
    {
        $genplan = $this->current();

        if (! $genplan) {
            return null;
        }

        return Quarter::query()
            ->select([
                'id', 'genplan_id', 'name', 'slug', 'description', 'status', 'polygon_data',
                'label_x', 'label_y', 'sort_order', 'is_active',
            ])
            ->whereBelongsTo($genplan)
            ->where('slug', $slug)
            ->publiclyVisible()
            ->first();
    }

    /** @return Collection<int, Plot> */
    public function plots(Quarter $quarter): Collection
    {
        return Plot::query()
            ->select([
                'id', 'quarter_id', 'number', 'slug', 'area', 'price', 'price_per_sotka',
                'status', 'polygon_data', 'marker_x', 'marker_y', 'description', 'image', 'is_visible',
            ])
            ->whereBelongsTo($quarter)
            ->publiclyVisible()
            ->orderBy('number')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, InfrastructurePoint> */
    public function infrastructure(?string $mode = null): Collection
    {
        $genplan = $this->current();

        if (! $genplan) {
            return new Collection;
        }

        return InfrastructurePoint::query()
            ->select([
                'id', 'genplan_id', 'name', 'category', 'icon', 'image', 'description',
                'marker_x', 'marker_y', 'show_on_3d', 'show_on_2d', 'sort_order', 'is_active',
            ])
            ->whereBelongsTo($genplan)
            ->active()
            ->when($mode === '2d', fn ($query) => $query->where('show_on_2d', true))
            ->when($mode === '3d', fn ($query) => $query->where('show_on_3d', true))
            ->orderBy('sort_order')
            ->orderBy('id')
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
}
