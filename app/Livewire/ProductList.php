<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteConfirmed' => 'delete'];

    public $search = '';
    public $productId;
    public $storeQuantities = []; // [store_id => quantity]
    public $store_id;
    public $name, $reference, $category_id, $brand_id, $unit_id, $purchase_price, $sale_price, $stock_quantity, $min_stock;
    public $isEditMode = false;

    public $store;


    public function render()
    {
        $user = Auth::user();

        $query = Product::with(['category', 'unit', 'stores'])
        ->where('tenant_id', Auth::user()->tenant_id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('reference', 'like', "%{$this->search}%");
            });
        }

        $products = $query->paginate(10);

        return view('livewire.product-list', [
            'products' => $products,
            'categories' => Category::where('tenant_id', Auth::user()->tenant_id)->get(),
            'brands' => Brand::where('tenant_id', Auth::user()->tenant_id)->get(),
            'units' => Unit::where('tenant_id', Auth::user()->tenant_id)->get(),
            'stores' => Store::where('tenant_id', Auth::user()->tenant_id)->get(),
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        // Initialiser storeQuantities pour tous les magasins lors de la création
        $stores = Store::where('tenant_id', Auth::user()->tenant_id)->get();
        $this->storeQuantities = $stores->mapWithKeys(fn($store) => [$store->id => 0])->toArray();
    }

    public function edit($id)
    {
        $product = Product::with('stores')->findOrFail($id);
        $this->productId = $id;
        $this->name = $product->name;
        $this->reference = $product->reference;
        $this->category_id = $product->category_id;
        $this->brand_id = $product->brand_id;
        $this->unit_id = $product->unit_id;
        $this->purchase_price = $product->purchase_price;
        $this->sale_price = $product->sale_price;
        $this->stock_quantity = $product->stock_quantity;
        $this->min_stock = $product->min_stock;

        // Récupérer les stocks existants
        $existingQuantities = $product->stores->mapWithKeys(function ($store) {
            return [$store->id => $store->pivot->quantity];
        })->toArray();

        // Remplir les quantités manquantes avec 0 si non présentes dans le pivot
        $allStores = Store::where('tenant_id', Auth::user()->tenant_id)->pluck('id');
        $this->storeQuantities = $allStores->mapWithKeys(function ($storeId) use ($existingQuantities) {
            return [$storeId => $existingQuantities[$storeId] ?? 0];
        })->toArray();

        $this->isEditMode = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'reference' => 'required|string|max:50|unique:products,reference,' . $this->productId,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'storeQuantities.*' => 'nullable|integer|min:0', // Validation des stocks des magasins
        ];

        // Messages de validation traduits
        $messages = [
            'name.required' => __('product.nom_requis'),
            'reference.required' => __('product.reference_requise'),
            'reference.unique' => __('product.reference_unique'),
            'category_id.required' => __('product.categorie_requise'),
            'unit_id.required' => __('product.unite_requise'),
            'purchase_price.required' => __('product.prix_achat_requis'),
            'sale_price.required' => __('product.prix_vente_requis'),
            'purchase_price.numeric' => __('product.prix_achat_requis'), // Réutiliser si possible ou créer une nouvelle clé
            'sale_price.numeric' => __('product.prix_vente_requis'), // Réutiliser si possible
            'storeQuantities.*.integer' => __('product.stock_invalide'), // Supposant que vous ajouterez cette clé
            'storeQuantities.*.min' => __('product.stock_invalide'), // Supposant que vous ajouterez cette clé
            // ... autres messages si nécessaire (min:0, max:255, etc.)
        ];

        $this->validate($rules, $messages);

        $product = Product::updateOrCreate(
            ['id' => $this->productId],
            [
                'tenant_id' => Auth::user()->tenant_id,
                'name' => $this->name,
                'reference' => $this->reference,
                'category_id' => $this->category_id,
                'brand_id' => $this->brand_id,
                'unit_id' => $this->unit_id,
                'purchase_price' => $this->purchase_price,
                'sale_price' => $this->sale_price,
                'min_stock' => $this->min_stock ?? 0,
            ]
        );

        // Gestion des stocks par magasin
        $syncData = [];
        foreach ($this->storeQuantities as $storeId => $qty) {
            // S'assurer que la quantité est un entier non négatif avant de synchroniser
            $qty = max(0, (int) $qty);
            $syncData[$storeId] = ['quantity' => $qty];
        }
        $product->stores()->sync($syncData);

        // Notification de succès traduite
        $messageKey = $this->isEditMode ? 'product.produit_mis_a_jour' : 'product.produit_cree';
        notyf()->success(__($messageKey));

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }


    public function confirmDelete($id)
    {
        $this->productId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        try {
            Product::find($this->productId)->delete();
            // Notification de suppression traduite
            notyf()->success(__('product.produit_supprime'));
        } catch (\Exception $e) {
            // Gestion d'erreur (ex: clé étrangère) avec message traduit
            notyf()->error(__('product.erreur_produit'));
        }
    }

    private function resetInputFields()
    {
        $this->productId = null;
        $this->name = '';
        $this->reference = '';
        $this->category_id = '';
        $this->brand_id = '';
        $this->unit_id = '';
        $this->purchase_price = '';
        $this->sale_price = '';
        $this->stock_quantity = 0;
        $this->min_stock = 0;
        $this->storeQuantities = [];
    }
}
