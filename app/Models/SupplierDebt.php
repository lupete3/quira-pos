<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierDebt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'supplier_id',
        'amount',
        'description',
        'debt_date',
        'is_paid',
        'paid_date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
