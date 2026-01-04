<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Filters\UserFilters;
use App\Models\Permission;
use App\Traits\Auditable;
use AhsanDev\Support\Authorization\Authorizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $avatar
 * @property string|null $remember_token
 * @property array|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FavoriteProject[] $favorites
 * @property-read int|null $favorites_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User filter(\App\Http\Filters\UserFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use Authorizable, HasApiTokens, HasFactory, Notifiable, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'contract_end_date',
        'contract_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'contract_end_date' => 'date',
        'meta' => 'array',
    ];

    /**
     * Get user permissions
     *
     * @return array
     */
    public function allPermissions()
    {
        if ($this->id == 1) {
            return Permission::pluck('name');
        }

        $role = $this->roles->first();

        if (!$role) {
            return collect([]);
        }

        return $role->permissions->pluck('name');
    }

    /**
     * Get the user's avatar.
     *
     * @param  string  $value
     * @return string
     */
    public function getAvatarAttribute($value)
    {
        return $value ? '/'.$value : null;
    }

    /**
     * Determine if user has admin role.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('Admin');
    }

    /**
     * Determine if user has user role.
     *
     * @return bool
     */
    public function isUser()
    {
        return $this->hasRole('User');
    }

    /**
     * Get the user's favorite projects.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favorites()
    {
        return $this->hasMany(FavoriteProject::class);
    }

    /**
     * Get the countries assigned to this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function countries()
    {
        return $this->hasMany(UserCountry::class);
    }

    /**
     * Get user's performance target
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function target()
    {
        return $this->hasOne(UserTarget::class);
    }

    /**
     * Get user's focus (job seekers/employers)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function focus()
    {
        return $this->hasOne(UserFocus::class);
    }

    /**
     * Get assigned V11 users (job seekers/employers)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignments()
    {
        return $this->hasMany(UserAssignment::class, 'taskhub_user_id');
    }

    /**
     * Get assigned job seekers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedJobSeekers()
    {
        return $this->hasMany(UserAssignment::class, 'taskhub_user_id')->where('v11_user_type', 2);
    }

    /**
     * Get assigned employers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedEmployers()
    {
        return $this->hasMany(UserAssignment::class, 'taskhub_user_id')->where('v11_user_type', 1);
    }

    /**
     * Get the websites assigned to this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function websites()
    {
        return $this->belongsToMany(Website::class, 'user_websites')->withTimestamps();
    }

    /**
     * Get the employee record for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function employeeRecord()
    {
        return $this->hasOne(EmployeeRecord::class);
    }

    /**
     * Get attendance records for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get leave requests for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get payroll records for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get advance payments for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function advancePayments()
    {
        return $this->hasMany(AdvancePayment::class);
    }

    /**
     * Get working hours assignments for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workingHours()
    {
        return $this->hasMany(UserWorkingHours::class);
    }

    /**
     * Get current active working hours for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentWorkingHours()
    {
        return $this->hasOne(UserWorkingHours::class)
            ->where('is_active', true)
            ->where(function ($query) {
                $today = now()->format('Y-m-d');
                $query->where(function ($q) {
                    $q->whereNull('effective_from')
                      ->whereNull('effective_until');
                })->orWhere(function ($q) use ($today) {
                    $q->where(function ($from) use ($today) {
                        $from->whereNull('effective_from')
                             ->orWhere('effective_from', '<=', $today);
                    })->where(function ($until) use ($today) {
                        $until->whereNull('effective_until')
                              ->orWhere('effective_until', '>=', $today);
                    });
                });
            })
            ->latest();
    }

    /**
     * Apply all relevant filters.
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  App\Http\Filters\UserFilters  $filters
     * @return Builder
     */
    public function scopeFilter($query, UserFilters $filters)
    {
        return $filters->apply($query);
    }

    /**
     * The user is super admin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->id == 1 || $this->hasRole('Admin');
    }

    /**
     * The user is super admin.
     *
     * @return bool
     */
    public function isOtherUser(): bool
    {
        return true;
    }
}
