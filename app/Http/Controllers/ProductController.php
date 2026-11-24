<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Fiche détaillée d'un produit.
     */
    public function show(Product $product)
    {
        $user = Auth::user();

        abort_unless($product->tenant_id === $user->tenant_id, 404);

        $product->load(['category', 'brand', 'unit', 'stores']);

        $canSeePurchase = $user->role_id != 3;

        $totalStock = (int) $product->stores->sum('pivot.quantity');

        $salesQuery = SaleItem::where('product_id', $product->id);
        $stats = [
            'total_stock'      => $totalStock,
            'stock_sale_value' => $totalStock * (float) $product->sale_price,
            'stock_cost_value' => $canSeePurchase ? $totalStock * (float) $product->purchase_price : null,
            'margin'           => $canSeePurchase ? (float) $product->sale_price - (float) $product->purchase_price : null,
            'sold_quantity'    => (float) (clone $salesQuery)->sum('quantity'),
            'sales_revenue'    => (float) (clone $salesQuery)->sum('total_price'),
            'last_sale_date'   => (clone $salesQuery)->join('sales', 'sales.id', '=', 'sale_items.sale_id')->max('sales.sale_date'),
        ];

        $recentSales = SaleItem::with(['sale.client', 'sale.store'])
            ->where('product_id', $product->id)
            ->latest('id')
            ->limit(10)
            ->get();

        $recentPurchases = $canSeePurchase
            ? PurchaseItem::with(['purchase.supplier', 'purchase.store'])
                ->where('product_id', $product->id)
                ->latest('id')
                ->limit(10)
                ->get()
            : collect();

        return view('products.show', compact(
            'product',
            'stats',
            'recentSales',
            'recentPurchases',
            'canSeePurchase'
        ));
    }
}
