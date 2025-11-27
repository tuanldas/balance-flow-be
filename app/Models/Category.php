<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'category_type',
        'parent_id',
        'icon',
        'color',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Category belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Category has parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Relationship: Category has many subcategories
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Scope: Get only system categories
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope: Get only user categories
     */
    public function scopeUserCategories($query, $userId)
    {
        return $query->where('user_id', $userId)->where('is_system', false);
    }

    /**
     * Scope: Get categories by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('category_type', $type);
    }

    /**
     * Scope: Get only parent categories (no subcategories)
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }
}
