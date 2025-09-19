<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\SupplierJournal;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierJournalList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $supplier_id;

    public function render()
    {
        $suppliers = Supplier::all();

        $journalEntries = SupplierJournal::with('supplier')
            ->when($this->supplier_id, function ($query) {
                $query->where('supplier_id', $this->supplier_id);
            })
            ->latest('entry_date')
            ->paginate(10);

        return view('livewire.supplier-journal-list', compact('suppliers', 'journalEntries'));
    }

    public function updatingSupplierId()
    {
        $this->resetPage();
    }
}