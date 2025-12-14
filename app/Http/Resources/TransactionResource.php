<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $amount = (float) $this->amount;
        $isExpense = $this->category && $this->category->category_type === 'expense';

        return [
            'id' => $this->id,
            'amount' => $isExpense ? -abs($amount) : abs($amount),
            'raw_amount' => $amount,
            'merchant_name' => $this->merchant_name,
            'transaction_date' => $this->transaction_date->toIso8601String(),
            'notes' => $this->notes,
            'status' => $this->status,
            'category' => $this->when($this->relationLoaded('category'), function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'type' => $this->category->category_type,
                    'icon' => $this->category->icon,
                ];
            }),
            // Mock data for account (deferred feature)
            'account' => [
                'id' => null,
                'name' => 'Default',
                'last_4' => '0000',
            ],
            // Mock data for tags (deferred feature)
            'tags' => [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
