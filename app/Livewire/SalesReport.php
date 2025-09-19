<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class SalesReport extends Component
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
        $salesQuery = Sale::with('client')
            ->whereBetween('sale_date', [$this->start_date, $this->end_date]);

        $sales = $salesQuery->paginate(10);

        $totals = $salesQuery->selectRaw('SUM(total_amount) as total_amount, SUM(total_paid) as total_paid')->first();

        return view('livewire.sales-report', [
            'sales' => $sales,
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
