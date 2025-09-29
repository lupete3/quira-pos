<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Store;
use App\Models\Transfer;
use App\Models\TransferItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransferManager extends Component
{
    public $from_store_id;
    public $to_store_id;
    public $products = [];
    public $productsTo = [];
    public $cart = [];      // panier des articles à transférer
    public $cartQty = [];   // quantités saisies dans les inputs

    public function updatedFromStoreId()
    {
        if ($this->from_store_id) {
            $store = Store::find($this->from_store_id);

            $this->products = $store->products()
                ->withPivot('quantity')
                ->get();

            $this->cartQty = [];
            foreach ($this->products as $product) {
                // Initialisez la quantité à 0 par défaut
                $this->cartQty[$product->id] = 0;
            }
        } else {
            $this->products = [];
            $this->cartQty = [];
        }

        $this->cart = [];
    }  // quantités saisies dans les inputs

    public function updatedToStoreId()
    {
        if ($this->to_store_id) {
            $store = Store::find($this->to_store_id);

            $this->productsTo = $store->products()
                ->withPivot('quantity')
                ->get();

        } else {
            $this->productsTo = [];
        }

    }

    public function addAllToCart()
    {
        if (!$this->from_store_id || !$this->to_store_id) {
            $this->addError('store', __('Veuillez sélectionner les magasins source et destination.'));
            notyf()->error(__('Veuillez sélectionner les magasins source et destination.'));
            return;
        }

        $itemsAdded = 0;
        foreach ($this->cartQty as $productId => $qty) {
            $qty = (int) $qty; // S'assurer que la quantité est un entier

            if ($qty > 0) {
                $product = $this->products->where('id', $productId)->first();

                if (!$product) {
                    notyf()->error(__("Produit introuvable."));
                    continue; // Passer au produit suivant
                }

                // Vérifier si le pivot et la quantité sont valides
                // if (!$product->pivot || $qty > $product->pivot->quantity) {
                //     notyf()->error("Quantité invalide pour {$product->name}. Le stock disponible est de {$product->pivot->quantity}.");
                //     continue;
                // }

                // Ajouter ou mettre à jour le produit dans le panier
                $this->cart[$productId] = [
                    'product' => $product,
                    'quantity' => $qty,
                ];
                $itemsAdded++;
            }
        }

        if ($itemsAdded > 0) {
          $this->products = $this->products;
            notyf()->success(__("{$itemsAdded} article(s) ajouté(s) au panier de transfert."));
        } else {
            notyf()->info(__("Veuillez saisir des quantités pour les produits à transférer."));
        }
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
        notyf()->info(__("Article retiré du panier."));
    }

    public function validateTransfer()
    {
        if (empty($this->cart)) {
            $this->addError('cart', __('Aucun produit sélectionné pour le transfert.'));
            notyf()->error(__('Aucun produit sélectionné pour le transfert.'));
            return;
        }

        DB::transaction(function () {
            $transfer = Transfer::create([
                'from_store_id' => $this->from_store_id,
                'to_store_id'   => $this->to_store_id,
                'user_id'       => Auth::id(),
                'status'        => 'validated',
                'transfer_date' => now(),
            ]);

            foreach ($this->cart as $item) {
                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id'  => $item['product']->id,
                    'quantity'    => $item['quantity'],
                ]);

                // Décrémenter le stock du magasin source
                $item['product']->stores()->updateExistingPivot($this->from_store_id, [
                    'quantity' => DB::raw("quantity - {$item['quantity']}")
                ]);

                // Incrémenter le stock du magasin destination
                $item['product']->stores()->updateExistingPivot($this->to_store_id, [
                    'quantity' => DB::raw("quantity + {$item['quantity']}")
                ]);
            }
        });

        notyf()->success(__("Transfert validé avec succès !"));
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->from_store_id = null;
        $this->to_store_id = null;
        $this->products = [];
        $this->cart = [];
        $this->cartQty = [];
    }

    public function render()
    {
        $stores = Store::all();
        return view('livewire.transfer-manager', [
            'stores' => $stores,
            'products' => $this->products,
            'cart' => $this->cart,
        ]);
    }
}
