<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = ['tenant_id', 'name', 'description'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
