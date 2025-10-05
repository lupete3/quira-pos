<?php

namespace App\Livewire;

use App\Models\CashTransaction;
use App\Models\Client;
use App\Models\ClientDebt as Debt;
use App\Models\ClientJournal;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ClientDebtList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $selectedClient;
    public $salesUnpaid = [];
    public $selectedSale;
    public $payment_amount;
    public $payment_description;

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

    public function render()
    {
        $clients = Client::where('tenant_id', Auth::user()->tenant_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->where('debt', '>', 0)
            ->latest()
            ->paginate(10);

        return view('livewire.client-debt-list', compact('clients'));
    }

    public function selectClient($clientId)
    {
        $this->selectedClient = Client::find($clientId);

        // ventes non totalement payées
        $this->salesUnpaid = Sale::where('tenant_id', Auth::user()->tenant_id)->where('client_id', $clientId)
          ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
          ->whereColumn('total_paid', '<', 'total_amount')
          ->get();

        $this->selectedSale = null;
        $this->payment_amount = null;
        $this->payment_description = '';
    }

    public function savePayment()
    {
        $this->validate([
            'selectedSale'   => 'required|exists:sales,id',
            'payment_amount' => 'required|numeric|min:0.01',
        ]);

        $sale = Sale::findOrFail($this->selectedSale);

        // montant restant à payer
        $remaining = $sale->total_amount - $sale->total_paid;

        // si le montant proposé > restant, on limite
        $amountToPay = min($this->payment_amount, $remaining);

        if ($this->payment_amount > $amountToPay) {
           notyf()->error(__("Le montant proposé dépasse le reste à payer."));
           return;
        }

        DB::transaction(function () use ($sale, $amountToPay) {
            // enregistrement trace dette
            $debtRecord = Debt::create([
                'tenant_id' => Auth::user()->tenant_id,
                'client_id'   => $this->selectedClient->id,
                'amount'      => $amountToPay,
                'description' => 'Paiement partiel pour la vente #' . $sale->id . '. ' . $this->payment_description,
                'is_paid'     => true,
                'paid_date'   => now(),
            ]);

            // maj vente
            $sale->increment('total_paid', $amountToPay);

            // maj dette globale client
            $this->selectedClient->decrement('debt', $amountToPay);

            // Enregistrer l'entrée en caisse
            $store = Auth::user()->stores()->first();
            $cashRegister = $store->cashRegister;

            if ($amountToPay > 0) {
                // Création de la transaction IN
                CashTransaction::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'cash_register_id' => $cashRegister->id,
                    'type' => 'in',
                    'amount' => $amountToPay,
                    'description' => 'Paiement effectué sur la vente #' . $sale->id,
                    'user_id' => Auth::id(),
                ]);

                // Mise à jour du solde de la caisse
                $cashRegister->increment('current_balance', $amountToPay);

            }
        });

        notyf()->success(__('Paiement enregistré avec succès.'));
        $this->dispatch('close-modal');
        $this->reset(['selectedClient','salesUnpaid','selectedSale','payment_amount','payment_description']);
    }
}
