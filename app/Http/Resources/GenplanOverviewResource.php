<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GenplanOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'images' => [
                '3d' => Storage::disk('public')->url($this->image_3d),
                '2d' => Storage::disk('public')->url($this->image_2d),
                'mobile_3d' => $this->mobile_image_3d ? Storage::disk('public')->url($this->mobile_image_3d) : null,
                'mobile_2d' => $this->mobile_image_2d ? Storage::disk('public')->url($this->mobile_image_2d) : null,
            ],
            'dimensions' => [
                'width' => $this->original_width,
                'height' => $this->original_height,
            ],
            'quarters' => QuarterResource::collection($this->whenLoaded('quarters')),
        ];
    }
}
