<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'slug' => $this->slug,
            'area' => $this->area,
            'price' => $this->price,
            'price_per_sotka' => $this->price_per_sotka,
            'status' => $this->status->value,
            'polygon' => $this->polygon_data,
            'marker' => [
                'x' => $this->marker_x !== null ? (float) $this->marker_x : null,
                'y' => $this->marker_y !== null ? (float) $this->marker_y : null,
            ],
            'description' => $this->description,
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
        ];
    }
}
