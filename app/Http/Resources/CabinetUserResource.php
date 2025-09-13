<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CabinetUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cabinet_id' => $this->cabinet_id,
            'user_id' => $this->user_id,
            'role' => $this->role,
            'is_owner' => $this->is_owner,
            'joined_at' => $this->created_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'description' => $permission->description,
                    'category' => $permission->category,
                ];
                });
            }),
        ];
    }
}
