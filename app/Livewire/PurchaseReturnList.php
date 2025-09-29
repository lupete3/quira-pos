<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseReturnList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $purchase_id;
    public $product_id;
    public $quantity;
    public $reason;
    public $purchaseProducts = [];
    public $firstDebt = 0;

    public function updatedPurchaseId($value)
    {
        $this->purchaseProducts = [];
        if ($value) {
            $purchase = Purchase::with('items.product')->find($value);
            if ($purchase) {
                $this->purchaseProducts = $purchase->items;
            }
        }
    }

    public function render()
    {
        $purchaseReturns = PurchaseReturn::with('product')
          ->where('tenant_id', Auth::user()->tenant_id)
          ->orderBy('return_date', 'DESC')->paginate(10);
        return view('livewire.purchase-return-list', compact('purchaseReturns'));
    }

    public function create()
    {
        $this->resetInputFields();
    }

    public function save()
    {
        $this->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
            'reason'      => 'nullable|string',
        ]);

        $purchaseItem = PurchaseItem::where('purchase_id', $this->purchase_id)
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('product_id', $this->product_id)
            ->first();

        $this->firstDebt = $purchaseItem->purchase->total_amount - $purchaseItem->purchase->total_paid;

        if (!$purchaseItem || $this->quantity > $purchaseItem->quantity) {
            $this->addError('quantity', __('La quantité retournée ne peut pas dépasser la quantité achetée.'));
            return;
        }

        DB::transaction(function () use ($purchaseItem) {
            $purchase = $purchaseItem->purchase;

            // 🔹 1. Enregistrer le retour
            PurchaseReturn::create([
                'tenant_id' => Auth::user()->tenant_id,
                'purchase_id' => $this->purchase_id,
                'product_id'  => $this->product_id,
                'store_id'    => $purchase->store_id,
                'quantity'    => $this->quantity,
                'reason'      => $this->reason,
                'return_date' => now(),
            ]);

            // 🔹 2. Réduire le stock du produit dans le magasin concerné
            $product = Product::find($this->product_id);
            $product->stores()->updateExistingPivot($purchase->store_id, [
                'quantity' => DB::raw("quantity - {$this->quantity}")
            ]);

            // 🔹 3. Réduire la quantité dans le détail d’achat
            $refundAmount = $this->quantity * $purchaseItem->unit_price;
            $purchaseItem->decrement('quantity', $this->quantity);
            $purchaseItem->decrement('total_price', $refundAmount);

            if ($purchaseItem->quantity <= 0) {
                $purchaseItem->delete();
            }

            // 🔹 4. Recalculer le total de l’achat
            $newTotal = $purchase->items()->sum('total_price');

            // 🔹 5. Ajuster le payé & la dette
            $newPaid = $purchase->total_paid;

            if ($newPaid > $newTotal) {
                // Trop payé → on ajuste
                $newPaid = $newTotal;
            }

            $debt = max(0, $newTotal - $newPaid);

            $purchase->update([
                'total_amount' => $newTotal,
                'total_paid'   => $newPaid,
            ]);

            // 🔹 6. Ajuster la dette du fournisseur
            if ($purchase->supplier_id) {
                $newDebt = ($purchase->supplier->debt - $this->firstDebt) + $debt;
                $purchase->supplier->update([
                    'debt' => $newDebt,
                ]);
            }
        });

        notyf()->success(__('Retour d’achat enregistré avec succès.'));
        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->purchase_id = '';
        $this->product_id = '';
        $this->quantity   = '';
        $this->reason     = '';
        $this->purchaseProducts = [];
    }
}
