<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTenant;

class Sale extends Model
{
    use HasFactory, SoftDeletes;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'customer_id',
        'coupon_id',
        'payment_method',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'manual_discount',
        'total',
        'status',
        'notes',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'manual_discount' => 'json'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
