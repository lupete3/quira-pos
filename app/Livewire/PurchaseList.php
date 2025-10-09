<?php

namespace App\Livewire;

use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $selectedPurchase;

    public function render()
    {
        $tenantId = Auth::user()->tenant_id;

        $purchases = Purchase::with(['supplier', 'store'])
            ->where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->where('id', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.purchase-list', compact('purchases'));
    }

    public function viewDetails($purchaseId)
    {
        $this->selectedPurchase = Purchase::with(['items.product', 'supplier'])->find($purchaseId);
    }
}
