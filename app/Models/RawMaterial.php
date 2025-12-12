<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = [
        'tenant_id',
        'designation',
        'purchase_price',
        'min_stock',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_raw_materials')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany(RawMaterialTransaction::class);
    }
}
