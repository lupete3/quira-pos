<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Store;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    /**
     * Initial sync - Download all data for a store
     * 
     * @param int $storeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function initial($storeId)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Verify user has access to this store
        $store = Store::where('id', $storeId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$store) {
            return response()->json([
                'message' => 'Magasin non trouvé ou accès non autorisé',
            ], 404);
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
                // Get stock quantity from pivot
                $storeProduct = $product->stores()
                    ->where('store_id', $storeId)
                    ->first();

                $product->store_stock = $storeProduct?->pivot->quantity ?? 0;

                return $product;
            });

        // Get all categories, brands, units for this tenant
        $categories = Category::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'updated_at']);

        $brands = Brand::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'updated_at']);

        $units = Unit::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name', 'short_name', 'updated_at']);

        // Get all clients for this tenant
        $clients = Client::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return response()->json([
            'products' => ProductResource::collection($products),
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
            'clients' => ClientResource::collection($clients),
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Incremental sync - Get changes since last sync
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function incremental(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'last_sync' => 'required|date',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $storeId = $request->store_id;
        $lastSync = $request->last_sync;

        // Verify user has access to this store
        $store = Store::where('id', $storeId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$store) {
            return response()->json([
                'message' => 'Magasin non trouvé ou accès non autorisé',
            ], 404);
        }

        // Get updated products
        $updatedProducts = Product::whereIn('id', function ($query) use ($storeId) {
            $query->select('product_id')
                ->from('store_products')
                ->where('store_id', $storeId);
        })
            ->where('tenant_id', $tenantId)
            ->where('updated_at', '>', $lastSync)
            ->with(['category', 'brand', 'unit'])
            ->get()
            ->map(function ($product) use ($storeId) {
                $storeProduct = $product->stores()
                    ->where('store_id', $storeId)
                    ->first();

                $product->store_stock = $storeProduct?->pivot->quantity ?? 0;

                return $product;
            });

        // Get updated clients
        $updatedClients = Client::where('tenant_id', $tenantId)
            ->where('updated_at', '>', $lastSync)
            ->get();

        // Get updated categories, brands, units
        $updatedCategories = Category::where('tenant_id', $tenantId)
            ->where('updated_at', '>', $lastSync)
            ->get(['id', 'name', 'description', 'updated_at']);

        $updatedBrands = Brand::where('tenant_id', $tenantId)
            ->where('updated_at', '>', $lastSync)
            ->get(['id', 'name', 'description', 'updated_at']);

        $updatedUnits = Unit::where('tenant_id', $tenantId)
            ->where('updated_at', '>', $lastSync)
            ->get(['id', 'name', 'short_name', 'updated_at']);

        return response()->json([
            'products' => [
                'updated' => ProductResource::collection($updatedProducts),
                'deleted' => [], // Implement soft deletes tracking if needed
            ],
            'categories' => [
                'updated' => $updatedCategories,
                'deleted' => [],
            ],
            'brands' => [
                'updated' => $updatedBrands,
                'deleted' => [],
            ],
            'units' => [
                'updated' => $updatedUnits,
                'deleted' => [],
            ],
            'clients' => [
                'updated' => ClientResource::collection($updatedClients),
                'deleted' => [],
            ],
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Upload sales created offline
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadSales(Request $request)
    {
        $request->validate([
            'sales' => 'required|array',
            'sales.*.temporary_id' => 'required|string',
            'sales.*.client_id' => 'required|exists:clients,id',
            'sales.*.store_id' => 'required|exists:stores,id',
            'sales.*.total_amount' => 'required|numeric|min:0',
            'sales.*.total_paid' => 'required|numeric|min:0',
            'sales.*.sale_date' => 'required|date',
            'sales.*.items' => 'required|array|min:1',
            'sales.*.items.*.product_id' => 'required|exists:products,id',
            'sales.*.items.*.quantity' => 'required|numeric|min:1',
            'sales.*.items.*.unit_price' => 'required|numeric|min:0',
            'sales.*.items.*.total_price' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $successfulSales = [];
        $conflicts = [];

        foreach ($request->sales as $saleData) {
            DB::beginTransaction();

            try {
                // Verify store belongs to user's tenant
                $store = Store::where('id', $saleData['store_id'])
                    ->where('tenant_id', $tenantId)
                    ->first();

                if (!$store) {
                    $conflicts[] = [
                        'temporary_id' => $saleData['temporary_id'],
                        'error' => 'Magasin non autorisé',
                    ];
                    DB::rollBack();
                    continue;
                }

                // Check stock availability for all items
                $stockConflicts = [];
                foreach ($saleData['items'] as $item) {
                    $product = Product::find($item['product_id']);

                    if (!$product) {
                        $stockConflicts[] = [
                            'product_id' => $item['product_id'],
                            'error' => 'Produit non trouvé',
                        ];
                        continue;
                    }

                    $storeProduct = $product->stores()
                        ->where('store_id', $saleData['store_id'])
                        ->first();

                    $availableStock = $storeProduct?->pivot->quantity ?? 0;

                    if ($availableStock < $item['quantity']) {
                        $stockConflicts[] = [
                            'product_id' => $item['product_id'],
                            'product_name' => $product->name,
                            'requested' => $item['quantity'],
                            'available' => $availableStock,
                        ];
                    }
                }

                // If there are stock conflicts, rollback and report
                if (!empty($stockConflicts)) {
                    $conflicts[] = [
                        'temporary_id' => $saleData['temporary_id'],
                        'type' => 'stock_insufficient',
                        'conflicts' => $stockConflicts,
                    ];
                    DB::rollBack();
                    continue;
                }

                // Create the sale (same logic as Pos.php)
                $sale = \App\Models\Sale::create([
                    'tenant_id' => $tenantId,
                    'client_id' => $saleData['client_id'],
                    'user_id' => $user->id,
                    'store_id' => $saleData['store_id'],
                    'total_amount' => $saleData['total_amount'],
                    'total_paid' => $saleData['total_paid'],
                    'sale_date' => $saleData['sale_date'],
                    'status' => 'validated',
                ]);

                // Create sale items and update stock
                foreach ($saleData['items'] as $item) {
                    \App\Models\SaleItem::create([
                        'tenant_id' => $tenantId,
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'total_price' => $item['total_price'],
                    ]);

                    // Update stock
                    $product = Product::find($item['product_id']);
                    $currentQty = $product->stores()
                        ->wherePivot('store_id', $saleData['store_id'])
                        ->first()
                        ->pivot
                        ->quantity;

                    $newQty = max(0, $currentQty - $item['quantity']);

                    $product->stores()->updateExistingPivot($saleData['store_id'], [
                        'quantity' => $newQty
                    ]);
                }

                // Update client debt if necessary
                $debt = $saleData['total_amount'] - $saleData['total_paid'];
                if ($debt > 0) {
                    $client = Client::find($saleData['client_id']);
                    $client->increment('debt', $debt);
                }

                // Update cash register
                $cashRegister = \App\Models\CashRegister::firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'store_id' => $saleData['store_id'],
                    ],
                    [
                        'opening_balance' => 0,
                        'current_balance' => 0,
                    ]
                );

                if ($saleData['total_paid'] > 0) {
                    \App\Models\CashTransaction::create([
                        'tenant_id' => $tenantId,
                        'cash_register_id' => $cashRegister->id,
                        'type' => 'in',
                        'amount' => $saleData['total_paid'],
                        'description' => 'Vente #' . $sale->id . ' (Mobile)',
                        'user_id' => $user->id,
                    ]);

                    $cashRegister->increment('current_balance', $saleData['total_paid']);
                }

                DB::commit();

                $successfulSales[] = [
                    'temporary_id' => $saleData['temporary_id'],
                    'server_id' => $sale->id,
                    'status' => 'created',
                ];

            } catch (\Exception $e) {
                DB::rollBack();

                $conflicts[] = [
                    'temporary_id' => $saleData['temporary_id'],
                    'error' => 'Erreur lors de la création: ' . $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => $successfulSales,
            'conflicts' => $conflicts,
            'message' => count($successfulSales) . ' vente(s) synchronisée(s), ' . count($conflicts) . ' conflit(s)',
        ], 200);
    }
}
