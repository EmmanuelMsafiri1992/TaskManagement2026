<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegumeDeliveryTracking extends Model
{
    use HasFactory;

    protected $table = 'legume_delivery_tracking';

    protected $fillable = [
        'legume_order_id',
        'user_id',
        'status',
        'driver_name',
        'driver_phone',
        'vehicle_info',
        'delivery_address',
        'delivery_cost',
        'estimated_delivery',
        'actual_delivery',
        'notes',
        'failure_reason',
    ];

    protected $casts = [
        'delivery_cost' => 'decimal:2',
        'estimated_delivery' => 'datetime',
        'actual_delivery' => 'datetime',
    ];

    /**
     * Get the order
     */
    public function order()
    {
        return $this->belongsTo(LegumeOrder::class, 'legume_order_id');
    }

    /**
     * Get the user who created/updated this delivery
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending deliveries
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in transit deliveries
     */
    public function scopeInTransit($query)
    {
        return $query->whereIn('status', ['assigned', 'picked_up', 'in_transit']);
    }

    /**
     * Scope for completed deliveries
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope for failed deliveries
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('driver_name', 'like', "%{$search}%")
              ->orWhere('driver_phone', 'like', "%{$search}%")
              ->orWhere('delivery_address', 'like', "%{$search}%")
              ->orWhere('vehicle_info', 'like', "%{$search}%")
              ->orWhereHas('order', function ($q2) use ($search) {
                  $q2->where('order_number', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Get status badge styling
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-gray-100 text-gray-800',
            'assigned' => 'bg-blue-100 text-blue-800',
            'picked_up' => 'bg-indigo-100 text-indigo-800',
            'in_transit' => 'bg-yellow-100 text-yellow-800',
            'delivered' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'assigned' => 'Assigned',
            'picked_up' => 'Picked Up',
            'in_transit' => 'In Transit',
            'delivered' => 'Delivered',
            'failed' => 'Failed',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Check if delivery is complete
     */
    public function isComplete()
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if delivery is active (not completed or failed)
     */
    public function isActive()
    {
        return !in_array($this->status, ['delivered', 'failed']);
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered()
    {
        $this->status = 'delivered';
        $this->actual_delivery = now();
        $this->save();

        // Update order status
        $this->order->update([
            'order_status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }
}
