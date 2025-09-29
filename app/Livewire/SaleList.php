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
        $this->selectedSale = Sale::with(['items.product', 'client'])->find($saleId);
    }

    public function printInvoice()
    {
        if ($this->selectedSale) {
          $this->dispatch('facture-validee', url: route('invoice.print', ['sale' => $this->selectedSale->id]));
        }
    }
}
