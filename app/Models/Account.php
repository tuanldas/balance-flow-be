<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'account_type_id',
        'name',
        'balance',
        'currency',
        'icon',
        'color',
        'description',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Account belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Account belongs to AccountType
     */
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Relationship: Account has many Transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope: Get accounts by user
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get accounts by account type
     */
    public function scopeByAccountType($query, string $accountTypeId)
    {
        return $query->where('account_type_id', $accountTypeId);
    }
}
