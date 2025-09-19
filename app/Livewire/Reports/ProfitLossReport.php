<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;

class ProfitLossReport extends Component
{
    public $start_date;
    public $end_date;

    public $total_sales = 0;
    public $total_purchases = 0;
    public $total_expenses = 0;
    public $profit_brut = 0;
    public $profit_net = 0;

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->endOfMonth()->toDateString();
        $this->calculate();
    }

    public function updated($field)
    {
        $this->calculate();
    }

    public function calculate()
    {
        $this->total_sales = Sale::whereBetween('created_at', [$this->start_date, $this->end_date])->sum('total_amount');

        $this->total_purchases = Purchase::whereBetween('created_at', [$this->start_date, $this->end_date])->sum('total_amount');

        $this->total_expenses = Expense::whereBetween('expense_date', [$this->start_date, $this->end_date])->sum('amount');

        $this->profit_brut = $this->total_sales - $this->total_purchases;
        $this->profit_net = $this->profit_brut;
        $this->profit_net = $this->profit_brut - $this->total_expenses;
    }

    public function exportPdf()
    {
        $data = [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_sales' => $this->total_sales,
            'total_purchases' => $this->total_purchases,
            'total_expenses' => $this->total_expenses,
            'profit_brut' => $this->profit_brut,
            'profit_net' => $this->profit_net,
        ];

        $pdf = Pdf::loadView('exports.profit_loss', $data)->setPaper('a4', 'portrait');
        return response()->streamDownload(fn() => print($pdf->output()), "rapport_profit_pertes.pdf");
    }

    public function render()
    {
        return view('livewire.reports.profit-loss-report');
    }
}

