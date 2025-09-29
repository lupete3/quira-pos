<?php

namespace App\Livewire;

use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $inventories = Inventory::with(['user','store'])->where('tenant_id', Auth::user()->tenant_id)->latest()->paginate(10);
        return view('livewire.inventory-list', compact('inventories'));
    }
}
