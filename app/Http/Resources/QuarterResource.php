<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuarterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $geometry = $this->geometries->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'geometry' => $geometry ? [
                'mode' => $geometry->mode->value,
                'polygon' => $geometry->polygon_data,
                'label' => [
                    'x' => $geometry->label_x !== null ? (float) $geometry->label_x : null,
                    'y' => $geometry->label_y !== null ? (float) $geometry->label_y : null,
                ],
            ] : null,
        ];
    }
}
