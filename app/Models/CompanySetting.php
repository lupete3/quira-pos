<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'logo',
        'rccm',
        'id_nat',
        'devise',
    ];
}
