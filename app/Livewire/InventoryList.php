<?php

namespace App\Livewire;

use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $inventories = Inventory::with(['user','store'])->latest()->paginate(10);
        return view('livewire.inventory-list', compact('inventories'));
    }
}
