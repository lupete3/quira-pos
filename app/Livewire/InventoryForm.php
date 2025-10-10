<?php

namespace App\Livewire;

use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InventoryForm extends Component
{
    public $stores;
    public $selectedStoreId;
    public $products;
    public $physical_quantities = [];

    public function mount()
    {
        // Charger tous les magasins une seule fois
        $this->stores = Store::where('tenant_id', Auth::user()->tenant_id)->get();
    }

    public function updatedSelectedStoreId($value)
    {
        // Réinitialiser les produits et les quantités si un magasin est sélectionné ou non
        $this->products = collect(); // Utiliser une collection vide
        $this->physical_quantities = [];

        if ($value) {
            // Charger les produits qui ont un stock dans ce magasin
            $this->products = Product::whereHas('stores', function ($query) use ($value) {
                $query->where('stores.id', $value);
            })->with(['stores' => function ($query) use ($value) {
                $query->where('stores.id', $value);
            }])->get();

            // Initialiser les quantités physiques avec les quantités théoriques
            foreach ($this->products as $product) {
                $theoretical_quantity = $product->stores->where('id', $value)->first()->pivot->quantity ?? 0;
                // Assurez-vous que c'est un entier
                $this->physical_quantities[$product->id] = intval($theoretical_quantity); 
            }
            
            // Si vous voulez charger tous les produits même ceux sans stock (à adapter selon votre besoin)
            // if ($this->products->isEmpty()) {
            //     notyf()->warning(__('inventory_form.aucun_produit'));
            // }
        }
    }

    public function render()
    {
        return view('livewire.inventory-form');
    }

    public function saveInventory()
    {
        if (!$this->selectedStoreId) {
            notyf()->error(__('inventory_form.erreur_magasin_non_selectionne'));
            return;
        }

        $rules = [
            'physical_quantities.*' => 'nullable|integer|min:0',
        ];
        
        $messages = [
            'physical_quantities.*.integer' => __('inventory_form.quantite_invalide'),
            'physical_quantities.*.min'     => __('inventory_form.quantite_invalide'),
        ];

        $this->validate($rules, $messages);
        
        if ($this->products->isEmpty()) {
             notyf()->warning(__('inventory_form.aucun_produit'));
             return;
        }


        DB::transaction(function () {
            $inventory = Inventory::create([
                'tenant_id' => Auth::user()->tenant_id,
                'user_id' => Auth::id(),
                'store_id' => $this->selectedStoreId,
                'inventory_date' => now(),
                'status' => 'validated',
            ]);

            foreach ($this->products as $product) {
                $physical_quantity = intval($this->physical_quantities[$product->id] ?? 0);

                $theoretical_quantity = $product->stores->where('id', $this->selectedStoreId)->first()->pivot->quantity ?? 0;
                $difference = $physical_quantity - $theoretical_quantity;

                InventoryItem::create([
                    'inventory_id' => $inventory->id,
                    'product_id' => $product->id,
                    'physical_quantity' => $physical_quantity,
                    'theoretical_quantity' => $theoretical_quantity,
                    'difference' => $difference,
                    'store_id' => $this->selectedStoreId,
                ]);

                $product->stores()->updateExistingPivot($this->selectedStoreId, ['quantity' => $physical_quantity]);
            }
        });

        notyf()->success(__('inventory_form.inventaire_valide'));
        return redirect()->route('inventories.index');
    }
}
