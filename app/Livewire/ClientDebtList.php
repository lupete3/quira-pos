<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\ClientDebt as Debt;
use App\Models\ClientJournal;
use App\Models\Sale;
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

    public function render()
    {
        $clients = Client::where('name', 'like', '%' . $this->search . '%')
            ->where('debt', '>', 0)
            ->latest()
            ->paginate(10);

        return view('livewire.client-debt-list', compact('clients'));
    }

    public function selectClient($clientId)
    {
        $this->selectedClient = Client::find($clientId);

        // ventes non totalement payées
        $this->salesUnpaid = Sale::where('client_id', $clientId)
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

        DB::transaction(function () use ($sale, $amountToPay) {
            // enregistrement trace dette
            $debtRecord = Debt::create([
                'client_id'   => $this->selectedClient->id,
                'amount'      => $amountToPay,
                'description' => 'Paiement partiel pour la vente #' . $sale->id . '. ' . $this->payment_description,
                'is_paid'     => true,
                'paid_date'   => now(),
            ]);

            // journal
            ClientJournal::create([
                'client_id'  => $this->selectedClient->id,
                'debt_id'    => $debtRecord->id,
                'payment'    => $amountToPay,
                'description'=> 'Paiement affecté à la vente #' . $sale->id,
            ]);

            // maj vente
            $sale->increment('total_paid', $amountToPay);

            // maj dette globale client
            $this->selectedClient->decrement('debt', $amountToPay);
        });

        session()->flash('message', 'Paiement enregistré avec succès.');
        $this->dispatch('close-modal');
        $this->reset(['selectedClient','salesUnpaid','selectedSale','payment_amount','payment_description']);
    }
}
