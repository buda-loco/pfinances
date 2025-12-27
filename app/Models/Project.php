<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'start_date',
        'end_date',
        'budget',
        'color',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function totalSpent()
    {
        return $this->transactions()->where('amount', '<', 0)->sum('amount');
    }

    public function totalIncome()
    {
        return $this->transactions()->where('amount', '>', 0)->sum('amount');
    }
}
