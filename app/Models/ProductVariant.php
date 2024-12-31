<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($productVariant) {
            if (! $productVariant->sku) {
                $productVariant->sku = Str::random(10);
            }
            if (! $productVariant->barcode) {
                $productVariant->barcode = Str::random(10);
            }

            if (! $productVariant->sale_price || $productVariant->sale_price == 0) {
                $productVariant->sale_price = $productVariant->product->sale_price;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'discounted_price' => 'decimal:2',
        ];
    }

    public function generateSku(): string
    {
        $product = $this->product;
        $category = $product->category;
        $categoryCode = $category->code ?? Str::upper(Str::substr($category->name, 0, 3));

        $attributeCodes = $this->attributeValues()
            ->orderBy('attribute_group_id')
            ->get()
            ->map(function ($attributeValue) {
                return $attributeValue->code ?? Str::upper(Str::substr($attributeValue->value, 0, 3));
            })
            ->implode('-');

        $this->sku = $product->id.'-'.$categoryCode.'-'.$attributeCodes;
        $this->save();

        return $this->sku;
    }

    public function generateBarcode($length = 10): string
    {
        do {
            $barcode = mt_rand(1, 9).mt_rand(100000000, 999999999);
        } while (strlen($barcode) !== $length);

        $this->barcode = $barcode;
        $this->save();

        return $this->barcode;
    }

    public function scopeWithFullSku(Builder $query): Builder
    {
        return $query->addSelect(['full_sku' => function ($query) {
            $query->selectRaw("CONCAT(products.name, '-', product_variants.sku)")
                ->from('products')
                ->whereColumn('products.id', 'product_variants.product_id')
                ->limit(1);
        }]);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->singleFile()
            //->useFallbackUrl('/default_avatar.jpg')
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attributes');
    }

    public function variantAttributes(): HasMany
    {
        return $this->hasMany(ProductVariantAttribute::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }
}
