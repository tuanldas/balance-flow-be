<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
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
            'name' => $this->translated_name, // Use the accessor that handles translation
            'original_name' => $this->name, // Include original name for system categories (translation key)
            'type' => $this->type,
            'icon_url' => $this->icon_path ? Storage::disk('public')->url($this->icon_path) : null,
            'is_system' => $this->is_system,
            'user_id' => $this->user_id,
            'transaction_count' => $this->whenLoaded('transactions', fn () => $this->transactions->count()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
