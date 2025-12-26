<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratorFuel extends Model
{
    use HasFactory;

    protected $table = 'generator_fuel';

    protected $fillable = [
        'current_level',
        'tank_capacity',
        'reserve_fuel',
        'consumption_rate',
        'is_running',
        'last_started_at',
        'last_stopped_at',
        'last_updated_at',
    ];

    protected $casts = [
        'current_level' => 'decimal:2',
        'tank_capacity' => 'decimal:2',
        'reserve_fuel' => 'decimal:2',
        'consumption_rate' => 'decimal:2',
        'is_running' => 'boolean',
        'last_started_at' => 'datetime',
        'last_stopped_at' => 'datetime',
        'last_updated_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(GeneratorFuelLog::class, 'generator_fuel_id');
    }

    public function getFuelPercentageAttribute()
    {
        if ($this->tank_capacity <= 0) {
            return 0;
        }
        return round(($this->current_level / $this->tank_capacity) * 100, 1);
    }

    public function getIsLowFuelAttribute()
    {
        return $this->current_level <= $this->reserve_fuel;
    }
}
