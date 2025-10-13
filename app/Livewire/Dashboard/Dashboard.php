<?php

namespace App\Livewire\Dashboard;

use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Dashboard extends Component
{

  public $storeId = null; // null = tous les magasins

  protected $listeners = ['localeUpdatedGlobally' => 'refreshLocale'];

  public function refreshLocale($locale)
  {
      app()->setLocale($locale);
      $this->render();
  }

  public function mount()
  {
    if (Auth::user()->role_id == 1) {
      $this->storeId = null;
    } else {
      $store = Auth::user()->stores()->first();
      $this->storeId = $store?->id;
    }
  }

  public function updatedStoreId()
  {
    $weeklySales = Sale::selectRaw('YEAR(sale_date) as year, WEEK(sale_date) as week, SUM(total_paid) as total')
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->where('tenant_id', Auth::user()->tenant_id)
      ->whereYear('sale_date', Carbon::now()->year)
      ->groupBy('year', 'week')
      ->orderBy('year')
      ->orderBy('week')
      ->pluck('total', 'week');

    $labels = $weeklySales->keys()->map(fn($w) => 'Semaine ' . $w)->values();
    $data = $weeklySales->values()->map(fn($v) => (float)$v)->values();

    $this->dispatch('weeklySalesUpdated', labels: $labels, data: $data);
  }

  public function render()
  {
    // === VENTES AUJOURD’HUI VS HIER ===
    $todaySales = Sale::where('tenant_id', Auth::user()->tenant_id)
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->whereDate('sale_date', Carbon::today())
      ->sum('total_paid');

    $yesterdaySales = Sale::where('tenant_id', Auth::user()->tenant_id)
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->whereDate('sale_date', Carbon::yesterday())
      ->sum('total_paid');

    $salesGrowth = $yesterdaySales > 0
      ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100
      : 0;

    // === VENTES MOIS ACTUEL VS MOIS PASSÉ ===
    $currentMonthSales = Sale::where('tenant_id', Auth::user()->tenant_id)
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->whereMonth('sale_date', Carbon::now()->month)
      ->whereYear('sale_date', Carbon::now()->year)
      ->sum('total_paid');

    $lastMonthSales = Sale::where('tenant_id', Auth::user()->tenant_id)
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->whereMonth('sale_date', Carbon::now()->subMonth()->month)
      ->whereYear('sale_date', Carbon::now()->subMonth()->year)
      ->sum('total_paid');

    $monthSalesGrowth = $lastMonthSales > 0
      ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100
      : 0;

    // === ACHATS MOIS ACTUEL VS MOIS PASSÉ ===
    $currentMonthPurchases = Purchase::where('tenant_id', Auth::user()->tenant_id)
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->whereMonth('purchase_date', Carbon::now()->month)
      ->whereYear('purchase_date', Carbon::now()->year)
      ->sum('total_amount');

    $lastMonthPurchases = Purchase::where('tenant_id', Auth::user()->tenant_id)
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->whereMonth('purchase_date', Carbon::now()->subMonth()->month)
      ->whereYear('purchase_date', Carbon::now()->subMonth()->year)
      ->sum('total_amount');

    $monthPurchasesGrowth = $lastMonthPurchases > 0
      ? (($currentMonthPurchases - $lastMonthPurchases) / $lastMonthPurchases) * 100
      : 0;

    $weeklySales = Sale::selectRaw('YEAR(sale_date) as year, WEEK(sale_date) as week, SUM(total_paid) as total')
      ->where('tenant_id', Auth::user()->tenant_id)
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->whereYear('sale_date', Carbon::now()->year)
      ->groupBy('year', 'week')
      ->orderBy('year')
      ->orderBy('week')
      ->pluck('total', 'week');

    $recentSales = Sale::with('client')->where('tenant_id', Auth::user()->tenant_id)
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))->latest()->take(5)->get();
    $recentPurchases = Purchase::with('supplier')->where('tenant_id', Auth::user()->tenant_id)
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))->latest()->take(5)->get();

    $columns = Schema::getColumnListing('products');
    $columns = array_map(fn($col) => "products.$col", $columns);

    $popularProducts = Product::select(array_merge($columns, [DB::raw('SUM(sale_items.quantity) as total_sold')]))
        ->join('sale_items', 'products.id', '=', 'sale_items.product_id')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->when($this->storeId, fn($q) => $q->where('sales.store_id', $this->storeId))
        ->when(Auth::check() && Auth::user()->tenant_id, fn($q) => $q->where('sales.tenant_id', Auth::user()->tenant_id))
        ->groupBy($columns)
        ->orderByDesc('total_sold')
        ->take(6)
        ->get();


    $sales = Sale::selectRaw('DATE(sale_date) as date, SUM(total_paid) as total')
      ->where('tenant_id', Auth::user()->tenant_id)
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->whereBetween('sale_date', [Carbon::now()->subDays(30), Carbon::now()])
      ->groupBy('date')
      ->orderBy('date')
      ->get();

    $dates = $sales->pluck('date');
    $salesData = $sales->pluck('total');

    // 🔹 Solde réel de la caisse
    $store = $this->storeId ? Store::find($this->storeId) : null;
    $current_balance = null;

    if ($store) {
      $cashRegister = CashRegister::where('tenant_id', Auth::user()->tenant_id)->where('store_id', $store->id)->first();
      $current_balance = $cashRegister?->current_balance ?? 0;
    } else {
      $current_balance = CashRegister::where('tenant_id', Auth::user()->tenant_id)->get()->sum('current_balance'); // Prendre la première caisse trouvée
    }

    return view('livewire.dashboard.dashboard', [
      'salesData' => $salesData,
      'dates' => $dates,

      'todaySales' => $todaySales,
      'salesGrowth' => $salesGrowth,

      'currentMonthSales' => $currentMonthSales,
      'monthSalesGrowth' => $monthSalesGrowth,

      'currentMonthPurchases' => $currentMonthPurchases,
      'monthPurchasesGrowth' => $monthPurchasesGrowth,

      'weeklySales' => $weeklySales,
      'recentSales' => $recentSales,
      'recentPurchases' => $recentPurchases,
      'popularProducts' => $popularProducts,

      'current_balance' => $current_balance,

      'stores' => Store::where('tenant_id', Auth::user()->tenant_id)->get(),
    ]);
  }
}
