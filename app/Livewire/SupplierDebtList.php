<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\SupplierDebt as Debt;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierDebtList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $selectedSupplier;
    public $purchasesUnpaid = [];
    public $selectedPurchase;
    public $payment_amount;
    public $payment_description;

    public function render()
    {
        $suppliers = Supplier::where('tenant_id', Auth::user()->tenant_id)->where('name', 'like', '%' . $this->search . '%')
            ->where('debt', '>', 0)
            ->latest()
            ->paginate(10);

        return view('livewire.supplier-debt-list', compact('suppliers'));
    }

    public function selectSupplier($supplierId)
    {
        $this->selectedSupplier = Supplier::find($supplierId);

        // Achats non totalement payés
        $this->purchasesUnpaid = Purchase::where('tenant_id', Auth::user()->tenant_id)->where('supplier_id', $supplierId)
            ->whereColumn('total_paid', '<', 'total_amount')
            ->get();

        $this->selectedPurchase = null;
        $this->payment_amount = null;
        $this->payment_description = '';
    }

    public function savePayment()
    {
        // Définition des règles et des messages de validation traduits
        $rules = [
            'selectedPurchase' => 'required|exists:purchases,id',
            'payment_amount'   => 'required|numeric|min:0.01',
        ];

        $messages = [
            'selectedPurchase.required' => __('supplier_debt.selectionner_facture'), // Utilisé la clé la plus pertinente
            'payment_amount.required'   => __('customer_debt.montant_requis'), // Réutilisation d'une clé client/générique si non fournie ici
            'payment_amount.numeric'    => __('customer_debt.montant_numerique'),
            'payment_amount.min'        => __('customer_debt.montant_min'),
        ];

        $this->validate($rules, $messages);

        $purchase = Purchase::findOrFail($this->selectedPurchase);

        // montant restant
        $remaining = $purchase->total_amount - $purchase->total_paid;

        // Validation que le montant ne dépasse pas le reste à payer (même si min() est utilisé pour limiter)
        if ($this->payment_amount > $remaining) {
             // Ici, on pourrait utiliser une notification plus spécifique si on en avait une pour le fournisseur,
             // mais en attendant on notifie pour éviter un comportement inattendu si l'utilisateur met un gros montant.
             notyf()->warning(__('supplier_debt.montant_depasse'));
            $this->payment_amount = $remaining;
            return;
        }

        $amountToPay = min($this->payment_amount, $remaining);

        try {
            DB::transaction(function () use ($purchase, $amountToPay) {
                // Enregistrement dette fournisseur (trace)
                Debt::create([
                    'tenant_id'   => Auth::user()->tenant_id,
                    'supplier_id' => $this->selectedSupplier->id,
                    'amount'      => $amountToPay,
                    'description' => 'Paiement facture achat #' . $purchase->id . '. ' . $this->payment_description,
                    'is_paid'     => true,
                    'paid_date'   => now(),
                ]);

                // MAJ facture achat
                $purchase->increment('total_paid', $amountToPay);

                // MAJ dette totale fournisseur
                $this->selectedSupplier->decrement('debt', $amountToPay);
            });

            // Notification de succès traduite
            notyf()->success(__('supplier_debt.paiement_enregistre'));
            $this->dispatch('close-modal');
            $this->reset(['selectedSupplier','purchasesUnpaid','selectedPurchase','payment_amount','payment_description']);
        } catch (\Exception $e) {
            // Notification d'erreur générale traduite
            notyf()->error(__('supplier_debt.erreur_operation'));
        }
    }
}
