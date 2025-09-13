<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Permission $permission */
        $permission = $this->resource;

        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'description' => $permission->description,
            'category' => $permission->category,
            'is_active' => $permission->is_active,
            'created_at' => $permission->created_at,
            'updated_at' => $permission->updated_at,
        ];
    }
}
