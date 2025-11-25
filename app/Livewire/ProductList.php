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
  public $storeQuantities = [];

  public $name, $reference, $category_name, $brand_name, $unit_name,
    $purchase_price, $sale_price, $stock_quantity, $min_stock;

  public $isEditMode = false;

  public $categorySearch = '';
  public $brandSearch = '';
  public $unitSearch = '';

  public $categoryResults = [];
  public $brandResults = [];
  public $unitResults = [];

  private function tenantId()
  {
    return Auth::user()->tenant_id;
  }

  public function render()
  {
    $tenant = $this->tenantId();

    $products = Product::with(['category', 'unit', 'stores'])
      ->where('tenant_id', $tenant)
      ->when($this->search, function ($query) {
        $query->where(function ($q) {
          $q->where('name', 'like', "%{$this->search}%")
            ->orWhere('reference', 'like', "%{$this->search}%");
        });
      })
      ->paginate(10);

    return view('livewire.product-list', [
      'products'   => $products,
      'categories' => Category::all(),
      'brands'     => Brand::all(),
      'units'      => Unit::all(),
      'stores'     => Store::where('tenant_id', Auth::user()->tenant_id)->get(),
    ]);
  }

  public function create()
  {
    $this->resetInputFields();
    $this->isEditMode = false;

    $this->storeQuantities = Store::all()
      ->pluck('id')
      ->mapWithKeys(fn($id) => [$id => 0])
      ->toArray();
  }

  public function edit($id)
  {
    $product = Product::with('stores')->findOrFail($id);
    $this->isEditMode = true;
    $this->productId  = $id;

    $this->fill([
      'name'           => $product->name,
      'reference'      => $product->reference,
      'categorySearch'  => $product->category->name ?? '',
      'brandSearch'     => $product->brand->name ?? '',
      'unitSearch'      => $product->unit->name ?? '',
      'purchase_price' => $product->purchase_price,
      'sale_price'     => $product->sale_price,
      'stock_quantity' => $product->stock_quantity,
      'min_stock'      => $product->min_stock,
    ]);

    $this->category_name  = $product->category->name ?? '';
    $this->brand_name     = $product->brand->name ?? '';
    $this->unit_name      = $product->unit->name ?? '';

    $existing = $product->stores->pluck('pivot.quantity', 'id')->toArray();

    $this->storeQuantities = Store::all()
      ->pluck('id')
      ->mapWithKeys(fn($id) => [$id => $existing[$id] ?? 0])
      ->toArray();
  }

  public function save()
  {
    $tenant = $this->tenantId();

    $this->validate([
      'name'         => 'required|string|max:255',
      'reference'    => "required|string|max:50|unique:products,reference,{$this->productId},id,tenant_id,{$tenant}",
      'category_name' => 'required',
      'unit_name'    => 'required',
      'purchase_price' => 'required|numeric|min:0',
      'sale_price'     => 'required|numeric|min:0',
      'min_stock'      => 'nullable|integer|min:0',
      'storeQuantities.*' => 'nullable|integer|min:0',
    ]);

    $product = Product::updateOrCreate(
      ['id' => $this->productId],
      [
        'tenant_id'       => $tenant,
        'name'            => $this->name,
        'reference'       => $this->reference,
        'category_id'     => $this->getOrCreate(Category::class, $this->category_name),
        'brand_id'        => $this->getOrCreate(Brand::class, $this->brand_name),
        'unit_id'         => $this->getOrCreate(Unit::class, $this->unit_name),
        'purchase_price'  => $this->purchase_price,
        'sale_price'      => $this->sale_price,
        'min_stock'       => $this->min_stock ?? 0,
      ]
    );

    $product->stores()->sync(
      collect($this->storeQuantities)
        ->map(fn($q) => ['quantity' => max(0, (int)$q)])
        ->toArray()
    );

    notyf()->success(__($this->isEditMode ? 'product.produit_mis_a_jour' : 'product.produit_cree'));

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
      notyf()->success(__('product.produit_supprime'));
    } catch (\Exception $e) {
      notyf()->error(__('product.erreur_produit'));
    }
  }

  private function getOrCreate($model, $name)
  {
    if (!$name) return null;

    return $model::firstOrCreate(
      ['tenant_id' => $this->tenantId(), 'name' => trim($name)]
    )->id;
  }

  private function resetInputFields()
  {
    $this->reset([
      'productId',
      'name',
      'reference',
      'categorySearch',
      'brandSearch',
      'unitSearch',
      'purchase_price',
      'sale_price',
      'stock_quantity',
      'min_stock',
      'storeQuantities'
    ]);
  }

  // ============================
  //  AUTOCOMPLETE MÉTHODES
  // ============================

  private function searchModel($model, $term)
  {
    return $model::query()
      ->tenant($this->tenantId())   // <-- utilisation du scope
      ->where('name', 'like', "%{$term}%")
      ->limit(7)
      ->get()
      ->toArray();
  }

  public function updatedCategorySearch()
  {
    $this->categoryResults = $this->searchModel(Category::class, $this->categorySearch);
    $this->category_name   = $this->categorySearch;
  }

  public function chooseCategory($id, $name)
  {
    $this->category_name   = $name;
    $this->categorySearch  = $name;
    $this->categoryResults = [];
  }

  public function updatedBrandSearch()
  {
    $this->brandResults = $this->searchModel(Brand::class, $this->brandSearch);
    $this->brand_name   = $this->brandSearch;
  }

  public function chooseBrand($id, $name)
  {
    $this->brand_name   = $name;
    $this->brandSearch  = $name;
    $this->brandResults = [];
  }

  public function updatedUnitSearch()
  {
    $this->unitResults = $this->searchModel(Unit::class, $this->unitSearch);
    $this->unit_name   = $this->unitSearch;
  }

  public function chooseUnit($id, $name)
  {
    $this->unit_name   = $name;
    $this->unitSearch  = $name;
    $this->unitResults = [];
  }
}
