<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'attribute_group_id' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function attributeGroup()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }
}
