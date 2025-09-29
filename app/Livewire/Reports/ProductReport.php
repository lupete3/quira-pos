<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductReport extends Component
{
  use WithPagination;

  protected $paginationTheme = 'bootstrap';

  public $search = '';
  public $category_id = '';
  public $brand_id = '';
  public $stock_status = ''; // all, low, out
  public $min_price = '';
  public $max_price = '';
  public $selectedStoreId = '';
  public $stores = [];

  public function mount()
  {
    // Charger tous les magasins une seule fois
    $this->stores = Store::all();

    if (Auth::user()->role_id != 1) {
      $store = Auth::user()->stores()->first();
      $this->selectedStoreId = $store?->id; // sécurisation si pas de magasin
    }

  }

  public function render()
  {
    $products = $this->getFilteredProducts(true);

    // Statistiques calculées comme avant...
    // $total_products = Product::where('tenant_id', Auth::user()->tenant_id)->count();

    $total_products = DB::table('store_products')
      ->join('products', 'products.id', '=', 'store_products.product_id')
      ->when($this->selectedStoreId, function ($q) {
        $q->where('store_products.store_id', $this->selectedStoreId);
      })
      ->sum(DB::raw('store_products.quantity'));

    $total_stock_value = DB::table('store_products')
      ->join('products', 'products.id', '=', 'store_products.product_id')
      ->when($this->selectedStoreId, function ($q) {
        $q->where('store_products.store_id', $this->selectedStoreId);
      })
      ->sum(DB::raw('store_products.quantity * products.purchase_price'));

    $total_stock_potential = DB::table('store_products')
      ->join('products', 'products.id', '=', 'store_products.product_id')
      ->when($this->selectedStoreId, function ($q) {
        $q->where('store_products.store_id', $this->selectedStoreId);
      })
      ->sum(DB::raw('store_products.quantity * products.sale_price'));

    $low_stock = DB::table('store_products')
      ->join('products', 'products.id', '=', 'store_products.product_id')
      ->when($this->selectedStoreId, function ($q) {
        $q->where('store_products.store_id', $this->selectedStoreId);
      })
      ->whereColumn('store_products.quantity', '<=', 'products.stock_alert')
      ->count();

    return view('livewire.reports.product-report', [
      'products' => $products,
      'categories' => Category::where('tenant_id', Auth::user()->tenant_id)->get(),
      'brands' => Brand::where('tenant_id', Auth::user()->tenant_id)->get(),
      'total_products' => $total_products,
      'total_stock_value' => $total_stock_value,
      'total_stock_potential' => $total_stock_potential,
      'low_stock' => $low_stock,
    ]);
  }

  public function exportPdf()
  {
    $products = $this->getFilteredProducts(false); // ✅ pas de pagination

    $store_id = $this->selectedStoreId;

    $pdf = Pdf::loadView('exports.product-report', [
      'products' => $products,
      'store_id' => $store_id,
      'store' => $store_id ? Store::find($store_id) : null,
    ])->setPaper('a4', 'portrait');

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->output();
    }, "rapport-produits.pdf");
  }

  public function getFilteredProducts($paginate = true)
  {
    $query = Product::with(['category', 'brand', 'unit', 'stores'])
        ->where('tenant_id', Auth::user()->tenant_id);

    if ($this->selectedStoreId) {
      $query->whereHas('stores', function ($q) {
        $q->where('stores.id', $this->selectedStoreId);
      });
    }

    if ($this->search) {
      $query->where(function ($q) {
        $q->where('name', 'like', "%{$this->search}%")
          ->orWhere('reference', 'like', "%{$this->search}%");
      });
    }

    if ($this->category_id) {
      $query->where('category_id', $this->category_id);
    }

    if ($this->brand_id) {
      $query->where('brand_id', $this->brand_id);
    }

    // ✅ Filtre stock
    if ($this->stock_status == 'low') {
      $query->whereHas('stores', function ($q) {
        if ($this->selectedStoreId) {
          $q->where('store_id', $this->selectedStoreId);
        }
        $q->whereColumn('store_products.quantity', '<=', 'products.stock_alert');
      });
    } elseif ($this->stock_status == 'out') {
      $query->whereHas('stores', function ($q) {
        if ($this->selectedStoreId) {
          $q->where('store_id', $this->selectedStoreId);
        }
        $q->where('store_products.quantity', '=', 0);
      });
    }

    // ✅ Filtre prix
    if ($this->min_price) {
      $query->whereHas('stores', function ($q) {
        if ($this->selectedStoreId) {
          $q->where('store_id', $this->selectedStoreId);
        }
        $q->where('store_products.sale_price', '>=', $this->min_price);
      });
    }

    if ($this->max_price) {
      $query->whereHas('stores', function ($q) {
        if ($this->selectedStoreId) {
          $q->where('store_id', $this->selectedStoreId);
        }
        $q->where('store_products.sale_price', '<=', $this->max_price);
      });
    }

    return $paginate ? $query->paginate(10) : $query->get();
  }
}
