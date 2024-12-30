<?php

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CouponService
{
    /**
     * Yeni bir kupon oluşturur
     */
    public function create(array $data): Coupon
    {
        // Eğer kod belirtilmemişse otomatik oluştur
        if (!isset($data['code'])) {
            $data['code'] = $this->generateUniqueCode();
        }

        // Başlangıç tarihi belirtilmemişse şu anı kullan
        if (!isset($data['start_date'])) {
            $data['start_date'] = Carbon::now();
        }

        return Coupon::create($data);
    }

    /**
     * Kupon kodunu kontrol eder
     */
    public function check(string $code): array
    {
        $coupon = Coupon::where('code', $code)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();

        if (!$coupon) {
            return [
                'success' => false,
                'message' => 'Geçersiz kupon kodu'
            ];
        }

        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
            return [
                'success' => false,
                'message' => 'Kupon kullanım limiti dolmuş'
            ];
        }

        return [
            'success' => true,
            'coupon' => $coupon
        ];
    }

    /**
     * Kupon kullanımını kaydeder
     */
    public function markAsUsed(Coupon $coupon): void
    {
        if ($coupon->usage_limit) {
            $coupon->increment('usage_count');
        }
    }

    /**
     * Benzersiz bir kupon kodu oluşturur
     */
    protected function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }

    /**
     * İndirim tutarını hesaplar
     */
    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if ($coupon->discount_type === 'percentage') {
            return ($subtotal * $coupon->amount) / 100;
        }

        return min($coupon->amount, $subtotal);
    }
} 