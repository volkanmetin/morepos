<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasTenant;
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
    ];
}
