<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InfrastructurePointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $geometry = $this->geometries->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'icon' => $this->icon,
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'description' => $this->description,
            'geometry' => [
                'mode' => $geometry->mode->value,
                'marker' => ['x' => (float) $geometry->marker_x, 'y' => (float) $geometry->marker_y],
            ],
            'show_on_3d' => $this->show_on_3d,
            'show_on_2d' => $this->show_on_2d,
        ];
    }
}
