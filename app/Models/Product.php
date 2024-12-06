<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasTenant;
    use InteractsWithMedia;
    use Searchable;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'discounted_price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function firstMedia(): string
    {
        return ($image = $this->getFirstMedia('images')) ? $image->getUrl() : asset('assets/images/no-image.png');
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->firstMedia(),
        );
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->useFallbackUrl(asset('assets/images/no-image.png'))
            ->useFallbackUrl(asset('assets/images/no-image.png'), 'preview')
            //->useFallbackUrl('/default_avatar_thumb.jpg', 'thumb')
            //->useFallbackPath(public_path('/default_avatar.jpg'))
            //->useFallbackPath(public_path('/default_avatar_thumb.jpg'), 'thumb')
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('preview')
                    ->fit(Fit::Contain, 300, 300)
                    ->nonQueued();
            });
    }

    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        // Customize the data array...

        return $array;
    }
}
