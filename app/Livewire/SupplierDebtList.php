<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\SupplierDebt as Debt;
use App\Models\Purchase;
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
        $suppliers = Supplier::where('name', 'like', '%' . $this->search . '%')
            ->where('debt', '>', 0)
            ->latest()
            ->paginate(10);

        return view('livewire.supplier-debt-list', compact('suppliers'));
    }

    public function selectSupplier($supplierId)
    {
        $this->selectedSupplier = Supplier::find($supplierId);

        // Achats non totalement payés
        $this->purchasesUnpaid = Purchase::where('supplier_id', $supplierId)
            ->whereColumn('total_paid', '<', 'total_amount')
            ->get();

        $this->selectedPurchase = null;
        $this->payment_amount = null;
        $this->payment_description = '';
    }

    public function savePayment()
    {
        $this->validate([
            'selectedPurchase' => 'required|exists:purchases,id',
            'payment_amount'   => 'required|numeric|min:0.01',
        ]);

        $purchase = Purchase::findOrFail($this->selectedPurchase);

        // montant restant
        $remaining = $purchase->total_amount - $purchase->total_paid;
        $amountToPay = min($this->payment_amount, $remaining);

        DB::transaction(function () use ($purchase, $amountToPay) {
            // Enregistrement dette fournisseur (trace)
            Debt::create([
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

        session()->flash('message', 'Paiement fournisseur enregistré avec succès.');
        $this->dispatch('close-modal');
        $this->reset(['selectedSupplier','purchasesUnpaid','selectedPurchase','payment_amount','payment_description']);
    }
}
