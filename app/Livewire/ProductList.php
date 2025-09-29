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
        $this->storeQuantities = $product->stores->mapWithKeys(function ($store) {
            return [$store->id => $store->pivot->quantity];
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
        ];
        $this->validate($rules);

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
                'min_stock' => $this->min_stock,
                'storeQuantities.*' => 'nullable|integer|min:0',
            ]

        );

        // Gestion des stocks par magasin
        $syncData = [];
        foreach ($this->storeQuantities as $storeId => $qty) {
            $syncData[$storeId] = ['quantity' => $qty ?? 0];
        }
        $product->stores()->sync($syncData);

        notyf()->success(__($this->isEditMode ? 'Produit mis à jour avec succès.' : 'Produit créé avec succès.'));
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
        Product::find($this->productId)->delete();
        notyf()->success(__('Produit supprimé avec succès.'));
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
    }
}
