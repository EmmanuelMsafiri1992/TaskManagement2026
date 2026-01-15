<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratorFuelLog extends Model
{
    use HasFactory;

    protected $table = 'generator_fuel_logs';

    protected $fillable = [
        'user_id',
        'action',
        'amount',
        'level_before',
        'level_after',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'level_before' => 'decimal:2',
        'level_after' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
