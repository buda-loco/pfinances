<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaggingRule extends Model
{
    protected $fillable = [
        'name',
        'pattern',
        'category_id',
        'project_id',
        'field',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function matches($text)
    {
        return preg_match($this->pattern, $text);
    }
}
