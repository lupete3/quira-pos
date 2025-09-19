<?php

namespace App\Livewire;

use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;

class StoreProductList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $store;

    public function mount(Store $store)
    {
        $this->store = $store;
    }

    public function render()
    {
        $products = $this->store->products()
            ->with('category', 'unit')
            ->paginate(10);

        return view('livewire.store-product-list', compact('products'));
    }
}
