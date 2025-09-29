<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDebt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'amount',
        'description',
        'debt_date',
        'is_paid',
        'paid_date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
