<?php

namespace App\Models;

use App\Enums\AttributeGroupType;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttributeGroup extends Model
{
    use HasTenant;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => AttributeGroupType::class,
            'sort' => 'integer',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort');
    }
}
