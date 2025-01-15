<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Coupon;
use App\Enums\SettingKey;
use App\Services\SettingService;
use Illuminate\Support\Facades\DB;

class SaleService
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function recalculateTotals(Sale $sale)
    {
        // Alt toplamı hesapla (güncellenmiş item'lar üzerinden)
        $subtotal = $sale->items->sum('total');

        // İndirim hesapla
        $discountAmount = 0;

        // Kupon indirimi hesapla
        if ($sale->coupon_id) {
            $coupon = Coupon::find($sale->coupon_id);
            if ($coupon) {
                if ($coupon->discount_type == 'percentage') {
                    $discountAmount += $subtotal * ($coupon->amount / 100);
                } else {
                    $discountAmount += $coupon->amount;
                }
            }
        }

        // Manuel indirim hesapla
        if ($sale->manual_discount) {
            if ($sale->manual_discount['type'] == 'percentage') {
                $discountAmount += $subtotal * ($sale->manual_discount['amount'] / 100);
            } else {
                $discountAmount += $sale->manual_discount['amount'];
            }
        }

        // İndirimli tutarı hesapla
        $discountedTotal = $subtotal - $discountAmount;

        // KDV hesapla
        $taxAmount = $discountedTotal * ($sale->tax_rate / 100);

        // Genel toplamı hesapla
        $total = $discountedTotal + $taxAmount;

        ray([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $total
        ]);

        // Satışı güncelle
        $sale->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $total
        ]);


        return $sale->fresh();
    }
} 