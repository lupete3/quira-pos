<?php

namespace App\Livewire\Reports;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Purchase;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PurchaseReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $supplier_id = '';
    public $status = '';
    public $date_type = 'all'; // all, today, month, year, range
    public $start_date = '';
    public $end_date = '';

    public function render()
    {
        $query = Purchase::with('supplier','user')
          ->where('tenant_id', Auth::user()->tenant_id);

        // 🔍 Filtrer par fournisseur
        if ($this->supplier_id) {
            $query->where('supplier_id', $this->supplier_id);
        }

        // 🔍 Filtrer par statut
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // 🔍 Filtrer par période
        if ($this->date_type == 'today') {
            $query->whereDate('purchase_date', Carbon::today());
        } elseif ($this->date_type == 'month') {
            $query->whereMonth('purchase_date', Carbon::now()->month)
                  ->whereYear('purchase_date', Carbon::now()->year);
        } elseif ($this->date_type == 'year') {
            $query->whereYear('purchase_date', Carbon::now()->year);
        } elseif ($this->date_type == 'range' && $this->start_date && $this->end_date) {
            $query->whereBetween('purchase_date', [$this->start_date, $this->end_date]);
        }

        $purchases = $query->orderBy('purchase_date','desc')->paginate(10);

        // 📊 Statistiques
        $total_purchases = $query->count();
        $total_amount = $query->sum('total_amount');
        $total_paid = $query->sum('total_paid');
        $total_due = $total_amount - $total_paid;

        return view('livewire.reports.purchase-report', [
            'purchases' => $purchases,
            'suppliers' => Supplier::where('tenant_id', Auth::user()->tenant_id)->get(),
            'total_purchases' => $total_purchases,
            'total_amount' => $total_amount,
            'total_paid' => $total_paid,
            'total_due' => $total_due,
        ]);
    }

    public function exportPdf()
    {
        $query = Purchase::with('supplier','user')
          ->where('tenant_id', Auth::user()->tenant_id);

        if ($this->supplier_id) $query->where('supplier_id', $this->supplier_id);
        if ($this->status) $query->where('status', $this->status);
        if ($this->date_type == 'today') $query->whereDate('purchase_date', Carbon::today());
        elseif ($this->date_type == 'month') $query->whereMonth('purchase_date', Carbon::now()->month)->whereYear('purchase_date', Carbon::now()->year);
        elseif ($this->date_type == 'year') $query->whereYear('purchase_date', Carbon::now()->year);
        elseif ($this->date_type == 'range' && $this->start_date && $this->end_date) $query->whereBetween('purchase_date', [$this->start_date, $this->end_date]);

        $purchases = $query->orderBy('purchase_date','desc')->get();

        $pdf = Pdf::loadView('exports.purchase-report', [
            'purchases' => $purchases
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, "rapport-achats.pdf");
    }
}

