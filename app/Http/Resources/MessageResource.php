<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'text' => $this->text,
            'url' => $this->url,
            'button_text' => $this->button_text,
            'button_url' => $this->button_url,
            'is_active' => $this->is_active,
            'is_persistent' => $this->is_persistent,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_read' => $this->userMessages->first()?->is_read ?? false,
            'read_at' => $this->userMessages->first()?->read_at,
        ];
    }
}
