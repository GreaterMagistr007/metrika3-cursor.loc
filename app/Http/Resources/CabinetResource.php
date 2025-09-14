<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CabinetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'owner' => $this->when($this->owner, [
                'id' => $this->owner?->id,
                'name' => $this->owner?->name,
                'phone' => $this->owner?->phone,
            ], null),
            'users_count' => $this->whenLoaded('cabinetUsers', function () {
                return $this->cabinetUsers->count();
            }, 0),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
