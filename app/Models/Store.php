<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = ['tenant_id', 'name', 'location', 'phone', 'email'];

    // Produits disponibles dans ce magasin
    public function products()
    {
        return $this->belongsToMany(Product::class, 'store_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    // Utilisateurs affectés
    public function users()
    {
        return $this->belongsToMany(User::class, 'store_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    // Ventes dans ce magasin
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Achats dans ce magasin
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    // Dépenses
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    // Caisses
    public function cashRegisters()
    {
        return $this->hasMany(CashRegister::class);
    }

    //Inventaires
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function cashRegister()
    {
        return $this->hasOne(CashRegister::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
