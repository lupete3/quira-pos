<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $fillable = ['cash_register_id', 'type', 'amount', 'description'];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }
}
