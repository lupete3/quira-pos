<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;

Route::get('/', function () {
  return redirect()->route('login');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
  ->middleware(['auth', 'verified', 'check.subscription'])
  ->name('dashboard');

Route::middleware(['auth', 'verified', 'check.subscription'])->group(function () {
    Route::get('plans', [PlanController::class, 'index'])->name('plan.index');
    Route::get('tenants', [TenantController::class, 'index'])->name('tenant.index');
    Route::get('souscription', [SubscriptionController::class, 'index'])->name('souscription.index');
});

Route::middleware(['auth', 'verified', 'check.subscription'])->group(function () {
  Route::redirect('settings', 'settings/profile');

  Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
  Volt::route('settings/password', 'settings.password')->name('settings.password');

  // Stores
  Route::get('stores', [StoreController::class, 'index'])->name('stores.index');
  Route::get('store-product/{store}', [StoreController::class, 'storeProduct'])->name('stores.listproducts');

  // Categories
  Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');

  // Units
  Route::get('units', [\App\Http\Controllers\UnitController::class, 'index'])->name('units.index');

  // Brands
  Route::get('brands', [\App\Http\Controllers\BrandController::class, 'index'])->name('brands.index');

  // Products
  Route::get('products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');

  // Clients
  Route::get('clients', [\App\Http\Controllers\ClientController::class, 'index'])->name('clients.index');

  // Suppliers
  Route::get('suppliers', [\App\Http\Controllers\SupplierController::class, 'index'])->name('suppliers.index');

  // POS
  Route::get('pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');

  // Sales
  Route::get('sales', [\App\Http\Controllers\SaleController::class, 'index'])->name('sales.index');
  Route::get('/invoice/{sale}', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoice.print');

  // Sale Returns
  Route::get('sale-returns', [\App\Http\Controllers\SaleReturnController::class, 'index'])->name('salereturns.index');

  // Purchases
  Route::get('purchases', [\App\Http\Controllers\PurchaseController::class, 'index'])->name('purchases.index');
  Route::get('purchases/create', [\App\Http\Controllers\PurchaseController::class, 'create'])->name('purchases.create');

  // Purchase Returns
  Route::get('purchase-returns', [\App\Http\Controllers\PurchaseReturnController::class, 'index'])->name('purchasereturns.index');

  // Client Debts
  Route::get('client-debts', [\App\Http\Controllers\ClientDebtController::class, 'index'])->name('clientdebts.index');

  // Supplier Debts
  Route::get('supplier-debts', [\App\Http\Controllers\SupplierDebtController::class, 'index'])->name('supplierdebts.index');

  // Roles
  Route::get('roles', [\App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');

  // Expense Category
  Route::get('expense-category', [\App\Http\Controllers\ExpenseCategoryController::class, 'index'])->name('expensecategory.index');
  Route::get('expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');

  // Users
  Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');

  // Transfers
  Route::get('transferts', [\App\Http\Controllers\TransferController::class, 'index'])->name('transfers.index');
  Route::get('transferts-create', [\App\Http\Controllers\TransferController::class, 'create'])->name('transfers.create');

  // Inventories
  Route::get('inventories', [\App\Http\Controllers\InventoryController::class, 'index'])->name('inventories.index');
  Route::get('inventories/create', [\App\Http\Controllers\InventoryController::class, 'create'])->name('inventories.create');
  Route::get('inventories/{inventory}', [\App\Http\Controllers\InventoryController::class, 'show'])->name('inventories.show');
  Route::get('inventories-export/{inventory}', [\App\Http\Controllers\InventoryController::class, 'export'])->name('inventories.export');

  // Reports
  Route::get('reports/products', [\App\Http\Controllers\ReportController::class, 'products'])->name('reports.products');
  Route::get('reports/sales', [\App\Http\Controllers\ReportController::class, 'sales'])->name('reports.sales');
  Route::get('reports/purchases', [\App\Http\Controllers\ReportController::class, 'purchases'])->name('reports.purchases');
  Route::get('reports/curstomers', [\App\Http\Controllers\ReportController::class, 'customers'])->name('reports.customers');
  Route::get('reports/suppliers', [\App\Http\Controllers\ReportController::class, 'suppliers'])->name('reports.suppliers');
  Route::get('reports/stock', [\App\Http\Controllers\ReportController::class, 'stock'])->name('reports.stock');
  Route::get('reports/expenses', [\App\Http\Controllers\ReportController::class, 'expense'])->name('reports.expense');
  Route::get('reports/cashregister', [\App\Http\Controllers\ReportController::class, 'cash'])->name('reports.cash');
  Route::get('reports/profitloss', [\App\Http\Controllers\ReportController::class, 'prfitLoss'])->name('reports.profitloss');

  Route::get('/settings/company', [DashboardController::class, 'settings'])->name('company.settings');
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

require __DIR__ . '/auth.php';
