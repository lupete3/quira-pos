<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaleResource;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of sales
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $query = Sale::where('tenant_id', $tenantId)
            ->with(['client', 'user', 'store', 'items.product'])
            ->orderBy('sale_date', 'desc');

        // Filter by store if provided
        if ($request->has('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // Filter by date range if provided
        if ($request->has('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $sales = $query->paginate($perPage);

        return response()->json([
            'data' => SaleResource::collection($sales),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
        ], 200);
    }

    /**
     * Store a newly created sale
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'store_id' => 'required|exists:stores,id',
            'total_amount' => 'required|numeric|min:0',
            'total_paid' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Verify store belongs to user's tenant
        $store = Store::where('id', $request->store_id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$store) {
            return response()->json([
                'message' => 'Magasin non autorisé',
            ], 403);
        }

        // Verify client belongs to tenant
        $client = Client::where('id', $request->client_id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$client) {
            return response()->json([
                'message' => 'Client non autorisé',
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Check stock availability for all items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception('Produit #' . $item['product_id'] . ' non trouvé');
                }

                $storeProduct = $product->stores()
                    ->where('store_id', $request->store_id)
                    ->first();

                $availableStock = $storeProduct?->pivot->quantity ?? 0;

                if ($availableStock < $item['quantity']) {
                    throw new \Exception(
                        'Stock insuffisant pour ' . $product->name .
                        '. Demandé: ' . $item['quantity'] .
                        ', Disponible: ' . $availableStock
                    );
                }
            }

            // Create the sale
            $sale = Sale::create([
                'tenant_id' => $tenantId,
                'client_id' => $request->client_id,
                'user_id' => $user->id,
                'store_id' => $request->store_id,
                'total_amount' => $request->total_amount,
                'total_paid' => $request->total_paid,
                'sale_date' => $request->sale_date,
                'status' => 'validated',
            ]);

            // Create sale items and update stock
            foreach ($request->items as $item) {
                SaleItem::create([
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
                    ->wherePivot('store_id', $request->store_id)
                    ->first()
                    ->pivot
                    ->quantity;

                $newQty = max(0, $currentQty - $item['quantity']);

                $product->stores()->updateExistingPivot($request->store_id, [
                    'quantity' => $newQty
                ]);
            }

            // Update client debt if necessary
            $debt = $request->total_amount - $request->total_paid;
            if ($debt > 0) {
                $client->increment('debt', $debt);
            }

            // Update cash register
            $cashRegister = CashRegister::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'store_id' => $request->store_id,
                ],
                [
                    'opening_balance' => 0,
                    'current_balance' => 0,
                ]
            );

            if ($request->total_paid > 0) {
                CashTransaction::create([
                    'tenant_id' => $tenantId,
                    'cash_register_id' => $cashRegister->id,
                    'type' => 'in',
                    'amount' => $request->total_paid,
                    'description' => 'Vente #' . $sale->id . ' (Mobile)',
                    'user_id' => $user->id,
                ]);

                $cashRegister->increment('current_balance', $request->total_paid);
            }

            DB::commit();

            // Load relationships for response
            $sale->load(['client', 'user', 'store', 'items.product']);

            return response()->json([
                'message' => 'Vente créée avec succès',
                'data' => new SaleResource($sale),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erreur lors de la création de la vente',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified sale
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $sale = Sale::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->with(['client', 'user', 'store', 'items.product'])
            ->first();

        if (!$sale) {
            return response()->json([
                'message' => 'Vente non trouvée',
            ], 404);
        }

        return response()->json([
            'data' => new SaleResource($sale),
        ], 200);
    }
}
