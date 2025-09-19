<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'name',
        'category_id',
        'brand_id',
        'unit_id',
        'purchase_price',
        'sale_price',
        'stock_quantity',
        'min_stock',
        'stock_alert',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchases()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function transferItems()
    {
        return $this->hasMany(TransferItem::class);
    }

}
