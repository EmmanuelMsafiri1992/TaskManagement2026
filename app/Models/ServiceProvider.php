<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ServiceProvider extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guard = 'service_provider';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'national_id',
        'address',
        'specialty',
        'qualification',
        'status',
        'agreement_signed',
        'agreement_signed_at',
        'avatar',
        'hourly_rate',
        'bio',
        'meta',
        'total_agreed_amount',
        'payment_preference',
        'monthly_amount',
        'payment_method',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_branch',
        'mobile_money_provider',
        'mobile_money_number',
        'mobile_money_name',
        'total_paid',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'agreement_signed' => 'boolean',
        'agreement_signed_at' => 'datetime',
        'hourly_rate' => 'decimal:2',
        'total_agreed_amount' => 'decimal:2',
        'monthly_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'meta' => 'array',
    ];

    protected $appends = ['balance_remaining', 'payment_progress_percent'];

    public function agreements()
    {
        return $this->hasMany(ServiceProviderAgreement::class);
    }

    public function recordingSessions()
    {
        return $this->hasMany(RecordingSession::class);
    }

    public function lessonPlans()
    {
        return $this->hasMany(LessonPlan::class);
    }

    public function latestAgreement()
    {
        return $this->hasOne(ServiceProviderAgreement::class)->latestOfMany();
    }

    public function hasSignedAgreement(): bool
    {
        return $this->agreement_signed;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getAvatarAttribute($value)
    {
        return $value ? '/' . $value : null;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function completedPayments()
    {
        return $this->hasMany(Payment::class)->where('status', 'completed');
    }

    public function getBalanceRemainingAttribute()
    {
        return $this->total_agreed_amount - $this->total_paid;
    }

    public function getPaymentProgressPercentAttribute()
    {
        if ($this->total_agreed_amount <= 0) {
            return 0;
        }
        return round(($this->total_paid / $this->total_agreed_amount) * 100, 1);
    }

    public function getFormattedTotalAgreedAttribute()
    {
        return 'MK ' . number_format($this->total_agreed_amount, 2);
    }

    public function getFormattedTotalPaidAttribute()
    {
        return 'MK ' . number_format($this->total_paid, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return 'MK ' . number_format($this->balance_remaining, 2);
    }

    public function getPaymentDetailsAttribute()
    {
        if ($this->payment_method === 'bank') {
            return [
                'type' => 'Bank Transfer',
                'bank' => $this->bank_name,
                'account' => $this->bank_account_number,
                'name' => $this->bank_account_name,
                'branch' => $this->bank_branch,
            ];
        } elseif ($this->payment_method === 'mobile_money') {
            return [
                'type' => 'Mobile Money',
                'provider' => $this->mobile_money_provider,
                'number' => $this->mobile_money_number,
                'name' => $this->mobile_money_name,
            ];
        }
        return null;
    }
}
