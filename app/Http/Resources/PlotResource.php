<?php

namespace App\Http\Resources;

use App\Domain\Genplan\PlotPresentation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $geometry = $this->geometries->first();

        return [
            'number' => $this->number,
            'slug' => $this->slug,
            'area' => $this->area,
            'area_label' => PlotPresentation::area($this->area),
            'price' => $this->price,
            'price_label' => PlotPresentation::money($this->price),
            'price_per_sotka' => $this->price_per_sotka,
            'price_per_sotka_label' => PlotPresentation::moneyPerSotka($this->price_per_sotka),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'can_inquire' => $this->status->canInquire(),
            'geometry' => $geometry ? [
                'mode' => $geometry->mode->value,
                'polygon' => $geometry->polygon_data,
                'marker' => [
                    'x' => $geometry->marker_x !== null ? (float) $geometry->marker_x : null,
                    'y' => $geometry->marker_y !== null ? (float) $geometry->marker_y : null,
                ],
            ] : null,
            'description' => $this->description,
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
        ];
    }
}
