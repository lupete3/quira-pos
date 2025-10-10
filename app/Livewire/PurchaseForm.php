<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PurchaseForm extends Component
{
    public $search = '';
    public $cart = [];
    public $supplier_id;
    public $total_paid;

    public $total = 0;

    public $store_id;

    public function render()
    {
        $products = [];
        if (strlen($this->search) >= 2) {
            $products = Product::where('tenant_id', Auth::user()->tenant_id)
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('reference', 'like', '%' . $this->search . '%')
                ->take(5)
                ->get();
        }

        $suppliers = Supplier::where('tenant_id', Auth::user()->tenant_id)->get();
        $stores = Store::where('tenant_id', Auth::user()->tenant_id)->get();

        $this->calculateTotal();

        return view('livewire.purchase-form', compact('products', 'suppliers', 'stores'));
    }

    // Méthodes d'interaction avec le panier (inchangées)
    public function addToCart($productId)
    {
        $product = Product::findOrFail($productId);
        $index = $this->findItemInCart($productId);

        if ($index !== false) {
            $this->cart[$index]['quantity']++;
            $this->cart[$index]['subtotal'] = $this->cart[$index]['price'] * $this->cart[$index]['quantity'];
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->purchase_price,
                'quantity' => 1,
                'subtotal' => $product->purchase_price,
            ];
        }

        $this->search = '';
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity <= 0) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
            return;
        }
        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['subtotal'] = $this->cart[$index]['price'] * $this->cart[$index]['quantity'];
    }

    public function updatePrice($index, $price)
    {
        $this->cart[$index]['price'] = $price;
        $this->cart[$index]['subtotal'] = $this->cart[$index]['price'] * $this->cart[$index]['quantity'];
    }

    public function removeItem($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function cancelPurchase()
    {
        $this->clearCart();
        notyf()->warning("Achat annulé."); // Ceci est un message temporaire si vous n'avez pas de clé pour l'annulation.
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->supplier_id = null;
        $this->total_paid = null;
        $this->total = 0;
    }

    public function savePurchase()
    {
        // Règles de validation
        $rules = [
            'supplier_id' => 'required|exists:suppliers,id',
            'store_id' => 'required|exists:stores,id',
            'total_paid' => 'required|numeric|min:0',
        ];

        // Messages de validation traduits
        $messages = [
            'supplier_id.required' => __('purchase.fournisseur_requis'),
            'store_id.required' => __('purchase.magasin_requis'),
            'total_paid.required' => __('purchase.montant_paye_requis'),
            'total_paid.min' => __('purchase.montant_paye_min'),
        ];

        $this->validate($rules, $messages);

        if (empty($this->cart)) {
            // Notification d'erreur traduite si le panier est vide
            notyf()->error(__('purchase.ajouter_produits'));
            return;
        }

        try {
            DB::transaction(function () {
                $purchase = Purchase::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'supplier_id' => $this->supplier_id,
                    'store_id' => $this->store_id,
                    'user_id' => Auth::id(),
                    'total_amount' => $this->total,
                    'total_paid' => $this->total_paid,
                    'purchase_date' => now(),
                    'status' => 'validated',
                ]);

                foreach ($this->cart as $item) {
                    PurchaseItem::create([
                        'tenant_id' => Auth::user()->tenant_id,
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['subtotal'],
                    ]);

                    // Mise à jour du stock dans store_products (insertion ou mise à jour)
                    DB::table('store_products')
                        ->updateOrInsert(
                            ['store_id' => $this->store_id, 'product_id' => $item['id']],
                            ['quantity' => DB::raw("quantity + {$item['quantity']}"), 'updated_at' => now(), 'created_at' => now()]
                        );
                }

                // Mise à jour dette fournisseur
                $supplier = Supplier::find($this->supplier_id);
                $debt = $this->total - $this->total_paid;
                if ($debt > 0) {
                    $supplier->increment('debt', $debt);
                }
            });

            // Notification de succès traduite
            notyf()->success(__('purchase.achat_enregistre'));
            $this->clearCart();
        } catch (\Exception $e) {
            // Notification d'erreur générale traduite
            notyf()->error(__('purchase.erreur_operation'));
        }
    }

    private function findItemInCart($productId)
    {
        foreach ($this->cart as $index => $item) {
            if ($item['id'] == $productId) {
                return $index;
            }
        }
        return false;
    }

    private function calculateTotal()
    {
        $this->total = array_reduce($this->cart, function ($carry, $item) {
            return $carry + $item['subtotal'];
        }, 0);
    }
}
