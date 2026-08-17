<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SurroundingPlaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'category_symbol' => $this->category->symbol(),
            'coordinates' => $this->geographicPoint()?->numeric(),
            'description' => $this->description,
            'image_url' => $this->safeImagePath() ? Storage::disk('public')->url($this->safeImagePath()) : null,
            'external_url' => $this->safeExternalUrl(),
        ];
    }
}
