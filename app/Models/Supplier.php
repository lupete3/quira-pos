<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'address',
        'debt',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function debts()
    {
        return $this->hasMany(SupplierDebt::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
