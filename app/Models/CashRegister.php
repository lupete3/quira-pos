<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'store_id', 'opening_balance', 'current_balance'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function transactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(CashWithdrawal::class);
    }

    // 🔹 Recalcul automatique du solde
    public function recalcBalance()
    {
        $in = $this->transactions()->where('type', 'in')->sum('amount');
        $out = $this->transactions()->where('type', 'out')->sum('amount');
        $this->current_balance = $this->opening_balance + $in - $out;
        $this->save();
    }
}
