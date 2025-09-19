<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExpenseReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $category_id = '';
    public $store_id = '';
    public $date_type = 'all'; // all, today, month, year, range
    public $start_date = '';
    public $end_date = '';

    public $stores = [];
    public $categories = [];

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
        $this->stores = Store::all();
        $this->categories = ExpenseCategory::all();
    }

    public function render()
    {
        $query = Expense::with('category','store','user');

        if ($this->store_id) {
            $query->where('store_id', $this->store_id);
        }

        if ($this->category_id) {
            $query->where('expense_category_id', $this->category_id);
        }

        if ($this->date_type == 'today') {
            $query->whereDate('expense_date', Carbon::today());
        } elseif ($this->date_type == 'month') {
            $query->whereMonth('expense_date', Carbon::now()->month)
                  ->whereYear('expense_date', Carbon::now()->year);
        } elseif ($this->date_type == 'year') {
            $query->whereYear('expense_date', Carbon::now()->year);
        } elseif ($this->date_type == 'range' && $this->start_date && $this->end_date) {
            $query->whereBetween('expense_date', [$this->start_date, $this->end_date]);
        }

        if ($this->search) {
            $query->where('description', 'like', "%{$this->search}%");
        }

        $expenses = $query->orderBy('expense_date','desc')->paginate(10);

        $total_expenses = $query->count();
        $total_amount = $query->sum('amount');

        return view('livewire.reports.expense-report', [
            'expenses' => $expenses,
            'total_expenses' => $total_expenses,
            'total_amount' => $total_amount,
            'stores' => $this->stores,
            'categories' => $this->categories,
        ]);
    }

    public function exportPdf()
    {
        $query = Expense::with('category','store','user');

        if ($this->store_id) $query->where('store_id', $this->store_id);
        if ($this->category_id) $query->where('expense_category_id', $this->category_id);
        if ($this->date_type == 'today') $query->whereDate('expense_date', Carbon::today());
        elseif ($this->date_type == 'month') $query->whereMonth('expense_date', Carbon::now()->month)->whereYear('expense_date', Carbon::now()->year);
        elseif ($this->date_type == 'year') $query->whereYear('expense_date', Carbon::now()->year);
        elseif ($this->date_type == 'range' && $this->start_date && $this->end_date) $query->whereBetween('expense_date', [$this->start_date, $this->end_date]);

        if ($this->search) $query->where('description', 'like', "%{$this->search}%");

        $expenses = $query->orderBy('expense_date','desc')->get();

        $pdf = Pdf::loadView('exports.expenses-report', [
            'expenses' => $expenses,
            'store' => $this->store_id ? Store::find($this->store_id) : null,
            'category' => $this->category_id ? ExpenseCategory::find($this->category_id) : null,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, "rapport-depenses.pdf");
    }
}

