<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ProductVariant;

class CopyVariantImageFromRemoteToLocal implements ShouldQueue
{
    use Queueable;

    protected $variant;

    public function __construct(public string $url, public int $variantId)
    {
        $this->variant = ProductVariant::find($this->variantId);
    }

    public function handle(): void
    {
        $media = $this->variant->addMediaFromUrl($this->url)
                        ->toMediaCollection('images');

        $media->copy($this->variant->product, 'images');
    }
}
