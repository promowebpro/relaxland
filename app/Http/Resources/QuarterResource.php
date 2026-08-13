<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuarterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'polygon' => $this->polygon_data,
            'label' => [
                'x' => $this->label_x !== null ? (float) $this->label_x : null,
                'y' => $this->label_y !== null ? (float) $this->label_y : null,
            ],
        ];
    }
}
