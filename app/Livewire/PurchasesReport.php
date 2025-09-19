<?php

namespace App\Livewire;

use App\Models\Purchase;
use Livewire\Component;
use Livewire\WithPagination;

class PurchasesReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $start_date;
    public $end_date;

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $purchasesQuery = Purchase::with('supplier')
            ->whereBetween('purchase_date', [$this->start_date, $this->end_date]);

        $purchases = $purchasesQuery->paginate(10);
        
        $totals = $purchasesQuery->selectRaw('SUM(total_amount) as total_amount, SUM(total_paid) as total_paid')->first();

        return view('livewire.purchases-report', [
            'purchases' => $purchases,
            'total_amount' => $totals->total_amount ?? 0,
            'total_paid' => $totals->total_paid ?? 0,
        ]);
    }

    public function updating($property)
    {
        if (in_array($property, ['start_date', 'end_date'])) {
            $this->resetPage();
        }
    }
}