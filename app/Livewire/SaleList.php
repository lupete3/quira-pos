<?php

namespace App\Livewire;

use App\Models\Sale;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SaleList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $selectedSale;
    public $store_id;

    public function mount()
    {
      if (Auth::user()->role_id == 1) {
        $this->store_id = null;
      } else {
          $store = Auth::user()->stores()->first();
        $this->store_id = $store->id;
      }
    }

    public function render()
    {
        if ($this->store_id) {
          $sales = Sale::with(['client','store'])
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('store_id', $this->store_id)
            ->latest()
            ->paginate(10);
        }else{
          $sales = Sale::with(['client','store'])
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where(function ($query) {
                $query->where('id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(10);
        }

        $stores = Store::where('tenant_id', Auth::user()->tenant_id)->orderBy('name', 'asc')->get();

        return view('livewire.sale-list', compact('sales','stores'));
    }

    public function viewDetails($saleId)
    {
        // Utilisation d'un try-catch pour capturer une erreur si Sale n'existe pas,
        // et envoyer une notification traduite.
        try {
            $this->selectedSale = Sale::with(['items.product', 'client'])->findOrFail($saleId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->selectedSale = null;
            notyf()->error(__('sale.erreur_vente'));
        }
    }

    public function printInvoice()
    {
        if ($this->selectedSale) {
          // L'action ici n'a pas de notification d'erreur spécifique, mais on peut l'encapsuler.
          try {
            $this->dispatch('facture-validee', url: route('invoice.print', ['sale' => $this->selectedSale->id]));
          } catch (\Exception $e) {
            notyf()->error(__('sale.erreur_vente'));
          }
        }
    }
}
