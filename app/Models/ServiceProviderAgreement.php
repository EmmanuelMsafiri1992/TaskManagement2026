<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'agreement_version',
        'agreement_content',
        'ip_address',
        'user_agent',
        'signature',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
