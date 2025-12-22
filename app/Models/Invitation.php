<?php

namespace App\Models;

use App\Http\Filters\InvitationFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Invitation extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Apply all relevant filters.
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  App\Http\Filters\InvitationFilters  $filters
     * @return Builder
     */
    public function scopeFilter($query, InvitationFilters $filters)
    {
        return $filters->apply($query);
    }
}
