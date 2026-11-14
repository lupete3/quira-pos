<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of products for a specific store
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $storeId = $request->store_id;

        // Verify user has access to this store
        $userStore = $user->stores()->where('store_id', $storeId)->first();

        if (!$userStore) {
            return response()->json([
                'message' => 'Accès non autorisé à ce magasin',
            ], 403);
        }

        // Get products with stock for this store
        $products = Product::whereIn('id', function ($query) use ($storeId) {
            $query->select('product_id')
                ->from('store_products')
                ->where('store_id', $storeId);
        })
            ->where('tenant_id', $tenantId)
            ->with(['category', 'brand', 'unit'])
            ->get()
            ->map(function ($product) use ($storeId) {
                $storeProduct = $product->stores()
                    ->where('store_id', $storeId)
                    ->first();

                $product->store_stock = $storeProduct?->pivot->quantity ?? 0;

                return $product;
            });

        return response()->json([
            'data' => ProductResource::collection($products),
        ], 200);
    }

    /**
     * Display the specified product
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $storeId = $request->store_id;

        $product = Product::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->with(['category', 'brand', 'unit'])
            ->first();

        if (!$product) {
            return response()->json([
                'message' => 'Produit non trouvé',
            ], 404);
        }

        // Get stock for the specific store
        $storeProduct = $product->stores()
            ->where('store_id', $storeId)
            ->first();

        $product->store_stock = $storeProduct?->pivot->quantity ?? 0;

        return response()->json([
            'data' => new ProductResource($product),
        ], 200);
    }
}
