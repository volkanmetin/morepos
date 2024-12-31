<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTenant;
use Illuminate\Support\Str;
use App\Enums\SaleStatus;
use App\Enums\PaymentMethod;

class Sale extends Model
{
    use HasFactory, SoftDeletes;
    use HasTenant;

    protected $guarded = [];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'manual_discount' => 'json',
        'uuid' => 'string',
        'status' => SaleStatus::class,
        'payment_method' => PaymentMethod::class,
        'tenant_id' => 'integer',
        'user_id' => 'integer',
        'customer_id' => 'integer',
        'coupon_id' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

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
