<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            'user_id' => $this->user_id,
            'account_type_id' => $this->account_type_id,
            'name' => $this->name,
            'balance' => $this->balance,
            'currency' => $this->currency,
            'icon' => $this->icon,
            'color' => $this->color,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'account_type' => $this->whenLoaded('accountType', function () {
                return [
                    'id' => $this->accountType->id,
                    'name' => $this->accountType->name,
                    'icon' => $this->accountType->icon,
                    'color' => $this->accountType->color,
                ];
            }),
        ];
    }
}
