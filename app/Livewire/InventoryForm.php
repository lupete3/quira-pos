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
        $this->stores = Store::all();
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
                $this->physical_quantities[$product->id] = intval($theoretical_quantity); // Assurez-vous que c'est un entier
            }
        }
    }

    public function render()
    {
        // Le rendu de la vue est simple, car les données sont déjà gérées par d'autres méthodes
        return view('livewire.inventory-form');
    }

    public function saveInventory()
    {
        // Assurez-vous qu'un magasin est sélectionné avant de sauvegarder
        if (!$this->selectedStoreId) {
            notyf()->error('Veuillez sélectionner un magasin.');
            return;
        }

        $this->validate([
            'physical_quantities.*' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () {
            $inventory = Inventory::create([
                'user_id' => Auth::id(),
                'store_id' => $this->selectedStoreId,
                'inventory_date' => now(),
                'status' => 'validated',
            ]);

            foreach ($this->products as $product) {
                $physical_quantity = $this->physical_quantities[$product->id] ?? 0;

                // Récupérez la quantité théorique fraîchement pour chaque produit pour éviter les erreurs
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

                // Mettez à jour le stock dans la table pivot
                $product->stores()->updateExistingPivot($this->selectedStoreId, ['quantity' => $physical_quantity]);
            }
        });

        notyf()->success('Inventaire du magasin validé et stock mis à jour avec succès.');
        return redirect()->route('inventories.index');
    }
}
