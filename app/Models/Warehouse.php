<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasTenant;
    use SoftDeletes;

    protected $guarded = [];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
