<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
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
        'photo',
    ];

    public const PHOTO_PLACEHOLDER = 'assets/img/product-placeholder.svg';

    protected static function booted(): void
    {
        static::deleted(function (Product $product) {
            $product->deletePhotoFile();
        });
    }

    /**
     * URL publique de la photo, ou illustration par défaut si absente.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && File::exists(public_path($this->photo))) {
            return asset($this->photo);
        }

        return asset(self::PHOTO_PLACEHOLDER);
    }

    public function hasPhoto(): bool
    {
        return $this->photo && File::exists(public_path($this->photo));
    }

    public function deletePhotoFile(): void
    {
        if ($this->photo && File::exists(public_path($this->photo))) {
            File::delete(public_path($this->photo));
        }
    }

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

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

}
