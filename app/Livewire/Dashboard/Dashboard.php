<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Store;
use App\Models\StoreProduct;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{

  public $storeId = null; // null = tous les magasins

  public function updatedStoreId()
  {
      $weeklySales = Sale::selectRaw('YEAR(sale_date) as year, WEEK(sale_date) as week, SUM(total_paid) as total')
          ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
          ->whereYear('sale_date', Carbon::now()->year)
          ->groupBy('year', 'week')
          ->orderBy('year')
          ->orderBy('week')
          ->pluck('total', 'week');

      $labels = $weeklySales->keys()->map(fn($w) => 'Semaine '.$w)->values();
      $data = $weeklySales->values()->map(fn($v) => (float)$v)->values();

      $this->dispatch('weeklySalesUpdated', labels: $labels, data: $data);

  }


  public function render()
  {
    // === VENTES AUJOURD’HUI VS HIER ===
    $todaySales = Sale::when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
        ->whereDate('sale_date', Carbon::today())
        ->sum('total_paid');

    $yesterdaySales = Sale::when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
        ->whereDate('sale_date', Carbon::yesterday())
        ->sum('total_paid');

    $salesGrowth = $yesterdaySales > 0 
        ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 
        : 0;

    // === VENTES MOIS ACTUEL VS MOIS PASSÉ ===
    $currentMonthSales = Sale::when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
        ->whereMonth('sale_date', Carbon::now()->month)
        ->whereYear('sale_date', Carbon::now()->year)
        ->sum('total_paid');

    $lastMonthSales = Sale::when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
        ->whereMonth('sale_date', Carbon::now()->subMonth()->month)
        ->whereYear('sale_date', Carbon::now()->subMonth()->year)
        ->sum('total_paid');

    $monthSalesGrowth = $lastMonthSales > 0
        ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100
        : 0;

    // === ACHATS MOIS ACTUEL VS MOIS PASSÉ ===
    $currentMonthPurchases = Purchase::when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
        ->whereMonth('purchase_date', Carbon::now()->month)
        ->whereYear('purchase_date', Carbon::now()->year)
        ->sum('total_amount');

    $lastMonthPurchases = Purchase::when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
        ->whereMonth('purchase_date', Carbon::now()->subMonth()->month)
        ->whereYear('purchase_date', Carbon::now()->subMonth()->year)
        ->sum('total_amount');

    $monthPurchasesGrowth = $lastMonthPurchases > 0
        ? (($currentMonthPurchases - $lastMonthPurchases) / $lastMonthPurchases) * 100
        : 0;

    // === STOCK GLOBAL (tous les magasins ou 1 magasin) ===
    $totalProductsInStock = StoreProduct::when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
        ->sum('quantity');

    $weeklySales = Sale::selectRaw('YEAR(sale_date) as year, WEEK(sale_date) as week, SUM(total_paid) as total')
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->whereYear('sale_date', Carbon::now()->year)
      ->groupBy('year', 'week')
      ->orderBy('year')
      ->orderBy('week')
      ->pluck('total', 'week');

    $recentSales = Sale::with('client')->latest()->take(5)->get();
    $recentPurchases = Purchase::with('supplier')->latest()->take(5)->get();
    $popularProducts = Product::withCount('saleItems')
      ->orderByDesc('sale_items_count')
      ->take(6)
      ->get();

    $sales = Sale::selectRaw('DATE(sale_date) as date, SUM(total_paid) as total')
      ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
      ->whereBetween('sale_date', [Carbon::now()->subDays(30), Carbon::now()])
      ->groupBy('date')
      ->orderBy('date')
      ->get();

    $dates = $sales->pluck('date');
    $salesData = $sales->pluck('total');

    return view('livewire.dashboard.dashboard', [
      'salesData' => $salesData,
      'dates' => $dates,

      'todaySales' => $todaySales,
      'salesGrowth' => $salesGrowth,

      'currentMonthSales' => $currentMonthSales,
      'monthSalesGrowth' => $monthSalesGrowth,

      'currentMonthPurchases' => $currentMonthPurchases,
      'monthPurchasesGrowth' => $monthPurchasesGrowth,

      'totalProductsInStock' => $totalProductsInStock,

      'weeklySales' => $weeklySales,
      'recentSales' => $recentSales,
      'recentPurchases' => $recentPurchases,
      'popularProducts' => $popularProducts,

      'stores' => Store::all(),
    ]);
  }
}
