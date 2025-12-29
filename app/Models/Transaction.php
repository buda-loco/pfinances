<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'account_id',
        // 'user_id', // SECURITY: Removed from fillable to prevent mass assignment attacks
        'category_id',
        'project_id',
        'entity_type_id',
        'external_id',
        'transaction_date',
        'posted_date',
        'description',
        'user_description',
        'notes_and_codes',
        'location_tag',
        'amount',
        'currency',
        'amount_in_aud',
        'credit_debit',
        'code',
        'income_outcome',
        'merchant_name',
        'transaction_type',
        'budget_category',
        'project',
        'work_type',
        'work_code',
        'client_type',
        'owner_automatic',
        'owner_manual',
        'is_manual',
        'is_reconciled',
        'is_included',
        'metadata',
    ];

    // Guard user_id from mass assignment
    protected $guarded = ['user_id'];

    protected $casts = [
        'transaction_date' => 'date',
        'posted_date' => 'date',
        'amount' => 'decimal:2',
        'amount_in_aud' => 'decimal:2',
        'is_manual' => 'boolean',
        'is_reconciled' => 'boolean',
        'is_included' => 'boolean',
        'metadata' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class);
    }

    // Auto-categorize based on keywords
    public function autoCategorize(): void
    {
        if ($this->code || !$this->notes_and_codes) {
            return;
        }

        $categories = Category::where('is_active', true)->get();

        foreach ($categories as $category) {
            $keywords = $category->keywords_array;
            foreach ($keywords as $keyword) {
                if (stripos($this->notes_and_codes, $keyword) !== false) {
                    $this->code = $category->code;
                    $this->category_id = $category->id;
                    return;
                }
            }
        }
    }
}
