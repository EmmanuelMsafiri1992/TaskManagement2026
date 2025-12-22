<?php

namespace App\Models;

use AhsanDev\Support\Optionable;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use Optionable;

    /**
     * The roles that belong to the permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
