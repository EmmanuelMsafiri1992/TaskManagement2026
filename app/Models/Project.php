<?php

namespace App\Models;

use App\Http\Filters\ProjectFilters;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'company_id',
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'company_logo',
        'meta',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Determine if the project has remaining users.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function remainingUsers(): Attribute
    {
        return new Attribute(
            get: fn () => ($this->users->count() > 10) ? ($this->users->count() - 10) : 0,
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lists()
    {
        return $this->hasMany(ProjectList::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function members()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Get the candidates seen by users.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getIsFavoriteAttribute()
    {
        return DB::table('favorite_projects')->where('project_id', $this->id)->where('user_id', auth()->id())->first();
    }

    /**
     * Apply all relevant filters.
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  App\Http\Filters\ProjectFilters  $filters
     * @return Builder
     */
    public function scopeFilter($query, ProjectFilters $filters)
    {
        return $filters->apply($query);
    }
}
