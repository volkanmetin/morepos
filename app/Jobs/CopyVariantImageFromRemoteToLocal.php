<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ProductVariant;

class CopyVariantImageFromRemoteToLocal implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $url, public int $variantId){}

    public function handle(): void
    {
        $variant = ProductVariant::find($this->variantId);
        $media = $variant->addMediaFromUrl($this->url)->toMediaCollection('images');

        $cacheKey = 'product_image_' . $variant->product_id . '_' . md5($this->url);
        
        if (!cache()->has($cacheKey)) {
            $media->copy($variant->product, 'images');
            cache()->put($cacheKey, true, now()->addDays(7));
        }
    }
}
