<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCashRegister extends Model
{
    protected $fillable = [
        'store_id',
        'opening_balance',
        'current_balance',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
