<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'address',
        'email',
        'phone',
        'logo',
        'rccm',
        'id_nat',
        'devise',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
