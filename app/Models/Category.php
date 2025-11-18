<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Category extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'icon_path',
        'is_system',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    /**
     * Generate a new UUID v7 for the model.
     */
    public function newUniqueId(): string
    {
        return Uuid::uuid7()->toString();
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get the user that owns the category.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for the category.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the translated name for the category.
     * System categories use translation keys, user categories return the name as-is.
     */
    public function getTranslatedNameAttribute(): string
    {
        if ($this->is_system) {
            return __($this->name);
        }

        return $this->name;
    }

    /**
     * Scope a query to only include system categories.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope a query to only include user categories.
     */
    public function scopeUserCategories($query, string $userId)
    {
        return $query->where('user_id', $userId)->where('is_system', false);
    }

    /**
     * Scope a query to filter by type (income/expense).
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to get all accessible categories for a user (system + user's own).
     */
    public function scopeAccessibleByUser($query, string $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_system', true)
                ->orWhere('user_id', $userId);
        });
    }
}
