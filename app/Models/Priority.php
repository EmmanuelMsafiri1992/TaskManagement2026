<?php

namespace App\Models;

use AhsanDev\Support\Categorizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    use Categorizable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The Category Parent.
     *
     * @var string
     */
    protected static $parent = 'Task Priority';
}
