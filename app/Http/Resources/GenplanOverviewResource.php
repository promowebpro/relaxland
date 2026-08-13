<?php

namespace App\Http\Resources;

use App\Domain\Genplan\GenplanMode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GenplanOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $selectedMode = $this->selected_mode instanceof GenplanMode
            ? $this->selected_mode
            : GenplanMode::default();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'selected_mode' => $selectedMode->value,
            'available_modes' => array_column(GenplanMode::cases(), 'value'),
            'assets' => [
                '3d' => [
                    'desktop' => Storage::disk('public')->url($this->image_3d),
                    'mobile' => $this->mobile_image_3d && $this->mobile_image_3d_is_compatible
                        ? Storage::disk('public')->url($this->mobile_image_3d) : null,
                ],
                '2d' => [
                    'desktop' => Storage::disk('public')->url($this->image_2d),
                    'mobile' => $this->mobile_image_2d && $this->mobile_image_2d_is_compatible
                        ? Storage::disk('public')->url($this->mobile_image_2d) : null,
                ],
            ],
            'dimensions' => [
                'width' => $this->original_width,
                'height' => $this->original_height,
            ],
            'quarters' => QuarterResource::collection($this->whenLoaded('quarters')),
        ];
    }
}
