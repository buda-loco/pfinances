<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'color',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function categories()
    {
        return $this->hasMany(Category::class, 'group_id');
    }
}
