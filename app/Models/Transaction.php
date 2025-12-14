<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'merchant_name',
        'transaction_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Transaction belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Transaction belongs to Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope: Get transactions by user
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get transactions by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Get income transactions (based on category type)
     */
    public function scopeIncome($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('category_type', 'income');
        });
    }

    /**
     * Scope: Get expense transactions (based on category type)
     */
    public function scopeExpense($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('category_type', 'expense');
        });
    }

    /**
     * Check if transaction is income
     */
    public function isIncome(): bool
    {
        return $this->category && $this->category->category_type === 'income';
    }

    /**
     * Check if transaction is expense
     */
    public function isExpense(): bool
    {
        return $this->category && $this->category->category_type === 'expense';
    }

    /**
     * Get formatted amount (positive for income, negative for expense)
     */
    public function getFormattedAmountAttribute(): float
    {
        $amount = (float) $this->amount;

        return $this->isExpense() ? -abs($amount) : abs($amount);
    }
}
