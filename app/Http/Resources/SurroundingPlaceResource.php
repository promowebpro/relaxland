<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurroundingPlaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'coordinates' => ['latitude' => (float) $this->latitude, 'longitude' => (float) $this->longitude],
            'description' => $this->description,
            'external_url' => $this->external_url,
        ];
    }
}
