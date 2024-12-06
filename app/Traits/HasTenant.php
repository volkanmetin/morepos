<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasTenant
{
    protected static function bootHasTenant()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $builder->where('tenant_id', app('currentTenant')->id);
        });

        static::creating(function (Model $model) {
            if (! $model->tenant_id) {
                $model->tenant_id = app('currentTenant')->id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
