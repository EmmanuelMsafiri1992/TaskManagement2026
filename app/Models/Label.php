<?php

namespace App\Models;

use App\Http\Filters\LabelFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Apply all relevant filters.
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  App\Http\Filters\LabelFilters  $filters
     * @return Builder
     */
    public function scopeFilter($query, LabelFilters $filters)
    {
        return $filters->apply($query);
    }
}
