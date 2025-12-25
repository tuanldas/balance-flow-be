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
            'name' => $this->name,
            'transaction_date' => $this->transaction_date,
            'notes' => $this->notes,
            'category' => $this->when($this->relationLoaded('category'), function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'type' => $this->category->category_type,
                    'icon' => $this->category->icon,
                ];
            }),
            'account' => [
                'id' => null,
                'name' => 'Default',
                'last_4' => '0000',
            ],
            'tags' => [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
