<?php

namespace App\Livewire;

use App\Models\Sale;
use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;

class SaleList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $selectedSale;
    public $store_id;

    public function render()
    {
        if ($this->store_id) {
          $sales = Sale::with(['client','store'])
            ->where('store_id', $this->store_id)
            ->latest()
            ->paginate(10);
        }else{
          $sales = Sale::with(['client','store'])
            ->where(function ($query) {
                $query->where('id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(10);
        }

        $stores = Store::orderBy('name', 'asc')->get();

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
