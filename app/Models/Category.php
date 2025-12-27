<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'group_id',
        'category_type',
        'frollo_category',
        'daily_budget',
        'weekly_budget',
        'monthly_budget',
        'color',
        'icon',
        'keywords',
        'order',
        'is_active',
    ];

    protected $casts = [
        'daily_budget' => 'decimal:2',
        'weekly_budget' => 'decimal:2',
        'monthly_budget' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Parent category relationship
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Child categories relationship
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Transactions relationship
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Budgets relationship
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    // Category Group relationship
    public function group(): BelongsTo
    {
        return $this->belongsTo(CategoryGroup::class, 'group_id');
    }

    // Get keywords as array
    public function getKeywordsArrayAttribute(): array
    {
        return $this->keywords ? explode(',', $this->keywords) : [];
    }
}
