<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SaleReturnList extends Component
{
  use WithPagination;
  protected $paginationTheme = 'bootstrap';
  public $search = '';

  public $sale_id;
  public $product_id;
  public $quantity;
  public $reason;
  public $saleProducts = [];
  public $firstDebt;

  public $storeId;

  public function mount()
  {
    if (Auth::user()->role_id == 1) {
       $this->storeId = null;
    } else {
        $store = Auth::user()->stores()->first();
       $this->storeId = $store->id;
    }
  }

  public function updatedSaleId($value)
  {
      $this->saleProducts = [];

      if ($value) {
          $tenantId = Auth::user()->tenant_id;

          $sale = Sale::with('items.product')
              ->where('tenant_id', $tenantId)
              ->find($value);

          if ($sale) {
              $this->saleProducts = $sale->items;
          }
      }
  }

  public function render()
  {
      $tenantId = Auth::user()->tenant_id;

      $saleReturns = SaleReturn::with('product')
          ->where('tenant_id', $tenantId)
          ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
          ->orderBy('return_date', 'DESC')
          ->paginate(10);

      return view('livewire.sale-return-list', compact('saleReturns'));
  }

  public function create()
  {
    $this->resetInputFields();
  }

  public function save()
  {
      $this->validate([
          'sale_id' => 'required|exists:sales,id',
          'product_id' => 'required|exists:products,id',
          'quantity' => 'required|integer|min:1',
          'reason' => 'nullable|string',
      ]);

      $saleItem = SaleItem::where('tenant_id', Auth::user()->tenant_id)->where('sale_id', $this->sale_id)
          ->where('product_id', $this->product_id)
          ->first();

      $this->firstDebt = $saleItem->sale->total_amount - $saleItem->sale->total_paid;

      if (!$saleItem || $this->quantity > $saleItem->quantity) {
          $this->addError('quantity', __('La quantité retournée ne peut pas dépasser la quantité vendue.'));
          return;
      }

      DB::transaction(function () use ($saleItem) {
          $sale = $saleItem->sale;
          $refundAmount = $this->quantity * $saleItem->unit_price;

          // 🔹 1. Enregistrer le retour
          SaleReturn::create([
              'tenant_id' => Auth::user()->tenant_id,
              'sale_id'    => $this->sale_id,
              'product_id' => $this->product_id,
              'store_id'   => $sale->store_id,
              'quantity'   => $this->quantity,
              'reason'     => $this->reason,
              'return_date'=> now(),
          ]);

          // 🔹 2. Mettre à jour le stock du produit dans le magasin
          $store = $sale->store;
          $product = Product::find($this->product_id);
          $product->stores()->updateExistingPivot($store->id, [
              'quantity' => DB::raw("quantity + {$this->quantity}")
          ]);

          // 🔹 3. Réduire la quantité dans le détail de vente
          $saleItem->decrement('quantity', $this->quantity);
          $saleItem->decrement('total_price', $refundAmount);

          // Supprimer la ligne si plus de quantité
          if ($saleItem->quantity <= 0) {
              $saleItem->delete();
          }

          // 🔹 4. Recalculer le total de la vente
          $newTotal = $sale->items()->sum('total_price');

          // 🔹 5. Ajuster le montant payé et la dette
          $newPaid = $sale->total_paid;

          if ($newPaid > $newTotal) {
              // Trop payé, on ajuste
              $newPaid = $newTotal;
          }

          $debt = max(0, $newTotal - $newPaid);
          $newDebt = ($sale->client->debt - $this->firstDebt) + $debt;

          $sale->update([
              'total_amount' => $newTotal,
              'total_paid'   => $newPaid,
          ]);

          if ($sale->client_id) {
              $sale->client->update([
                  'debt' => $newDebt,
              ]);
          }
      });

      notyf()->success(__('Retour de vente enregistré avec succès.'));
      $this->dispatch('close-modal');
      $this->resetInputFields();
  }

  private function resetInputFields()
  {
    $this->sale_id = '';
    $this->product_id = '';
    $this->quantity = '';
    $this->reason = '';
    $this->saleProducts = [];
  }
}





