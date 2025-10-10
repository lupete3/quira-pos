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
            $tenantId = Auth::user()->tenant_id;

            $purchase = Purchase::with('items.product')
                ->where('tenant_id', $tenantId)
                ->find($value);

            if ($purchase) {
                $this->purchaseProducts = $purchase->items;
            } else {
                // Ajout d'une erreur si l'ID d'achat n'est pas trouvé
                $this->addError('purchase_id', __('purchase_return.purchase_id_requis'));
            }
        }
    }

    public function render()
    {
        $tenantId = Auth::user()->tenant_id;

        $purchaseReturns = PurchaseReturn::with('product')
            ->where('tenant_id', $tenantId)
            ->orderBy('return_date', 'DESC')
            ->paginate(10);

        return view('livewire.purchase-return-list', compact('purchaseReturns'));
    }

    public function create()
    {
        $this->resetInputFields();
    }

    public function save()
    {
        // Règles et messages de validation traduits
        $rules = [
            'purchase_id' => 'required|exists:purchases,id',
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
            'reason'      => 'nullable|string',
        ];

        $messages = [
            'purchase_id.required' => __('purchase_return.purchase_id_requis'),
            'purchase_id.exists'   => __('purchase_return.purchase_id_requis'), // Réutilisation pour l'existence
            'product_id.required'  => __('purchase_return.product_id_requis'),
            'quantity.required'    => __('purchase_return.quantite_requise'),
            'quantity.min'         => __('purchase_return.quantite_min'),
        ];

        $this->validate($rules, $messages);

        $purchaseItem = PurchaseItem::where('purchase_id', $this->purchase_id)
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('product_id', $this->product_id)
            ->first();

        // Récupérer la dette initiale pour le calcul de l'ajustement global
        if ($purchaseItem) {
             $this->firstDebt = $purchaseItem->purchase->total_amount - $purchaseItem->purchase->total_paid;
        }

        // Validation de la quantité à retourner vs quantité achetée
        if (!$purchaseItem || $this->quantity > $purchaseItem->quantity) {
            $this->addError('quantity', __('purchase_return.quantite_depasse'));
            return;
        }

        try {
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

                // 🔹 5. Ajuster le payé & la dette sur l'achat
                $newPaid = $purchase->total_paid;

                if ($newPaid > $newTotal) {
                    // Trop payé → on ajuste (le reste est un remboursement dû par le fournisseur)
                    $newPaid = $newTotal;
                }

                $debt = max(0, $newTotal - $newPaid);

                $purchase->update([
                    'total_amount' => $newTotal,
                    'total_paid'   => $newPaid,
                ]);

                // 🔹 6. Ajuster la dette du fournisseur
                if ($purchase->supplier_id) {
                    // Nouvelle dette du fournisseur = (Dette actuelle - Dette initiale de cet achat) + Nouvelle dette de cet achat
                    $newDebt = ($purchase->supplier->debt - $this->firstDebt) + $debt;
                    $purchase->supplier->update([
                        'debt' => $newDebt,
                    ]);
                }
            });

            // Notification de succès traduite
            notyf()->success(__('purchase_return.retour_enregistre'));
            $this->dispatch('close-modal');
            $this->resetInputFields();
        } catch (\Exception $e) {
            // Notification d'erreur générale traduite
            notyf()->error(__('purchase_return.erreur_operation'));
        }
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
