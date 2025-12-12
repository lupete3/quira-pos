<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRawMaterial extends Model
{
    protected $fillable = ['store_id', 'raw_material_id', 'quantity'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
