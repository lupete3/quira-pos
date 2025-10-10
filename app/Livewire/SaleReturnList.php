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

    // Méthode pour vider les produits lors de la mise à jour de l'ID de vente
    public function updatedSaleId($value)
    {
        $this->saleProducts = [];

        if ($value) {
            $tenantId = Auth::user()->tenant_id;

            $sale = Sale::with('items.product')
                ->where('tenant_id', $tenantId)
                ->find($value);

            // Si la vente n'est pas trouvée, on peut ajouter une erreur
            if (!$sale) {
                $this->addError('sale_id', __('sale_return.vente_existe_pas'));
            } else {
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
        // Règles de validation avec messages traduits
        $rules = [
            'sale_id' => 'required|exists:sales,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ];

        $messages = [
            'sale_id.required' => __('validation.required', ['attribute' => __('sale_return.id_vente')]), // Réutilisation d'une clé Laravel si elle existe, sinon...
            'sale_id.exists' => __('sale_return.vente_existe_pas'),
            'product_id.required' => __('sale_return.produit_requis'),
            'quantity.required' => __('sale_return.quantite_requise'),
            'quantity.min' => __('sale_return.quantite_min'),
        ];

        $this->validate($rules, $messages);

        $saleItem = SaleItem::where('tenant_id', Auth::user()->tenant_id)->where('sale_id', $this->sale_id)
            ->where('product_id', $this->product_id)
            ->first();

        // Récupérer la dette initiale avant toute modification
        $this->firstDebt = $saleItem->sale->total_amount - $saleItem->sale->total_paid;

        // Validation de la quantité à retourner vs quantité achetée
        if (!$saleItem || $this->quantity > $saleItem->quantity) {
            // Utilisation de la clé de traduction spécifique
            $this->addError('quantity', __('sale_return.quantite_invalide'));
            return;
        }

        try {
            DB::transaction(function () use ($saleItem) {
                $sale = $saleItem->sale;
                $refundAmount = $this->quantity * $saleItem->unit_price;

                // 🔹 1. Enregistrer le retour
                SaleReturn::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'sale_id' 	 => $this->sale_id,
                    'product_id' => $this->product_id,
                    'store_id' 	 => $sale->store_id,
                    'quantity' 	 => $this->quantity,
                    'reason' 	 => $this->reason,
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
                    // Si le montant payé est supérieur au nouveau total (remboursement dû au client)
                    $newPaid = $newTotal;
                }

                $debt = max(0, $newTotal - $newPaid);
                $newDebt = ($sale->client->debt - $this->firstDebt) + $debt; // Mise à jour de la dette globale du client

                $sale->update([
                    'total_amount' => $newTotal,
                    'total_paid' 	 => $newPaid,
                ]);

                if ($sale->client_id) {
                    $sale->client->update([
                        'debt' => $newDebt,
                    ]);
                }
            });

            // Notification de succès traduite
            notyf()->success(__('sale_return.retour_enregistre'));
            $this->dispatch('close-modal');
            $this->resetInputFields();
        } catch (\Exception $e) {
            // En cas d'erreur inattendue (ex: problème DB)
            notyf()->error(__('sale_return.erreur_operation'));
        }
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
