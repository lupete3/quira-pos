<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SalesReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $client_id = '';
    public $status = '';
    public $date_type = 'all'; // all, today, month, year, range
    public $start_date = '';
    public $end_date = '';

    public $stores = [];
    public $store_id;

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
        $this->stores = Store::where('tenant_id', Auth::user()->tenant_id)->get();

        if (Auth::user()->role_id != 1) {
          $store = Auth::user()->stores()->first();
          $this->store_id = $store?->id; // sécurisation si pas de magasin
        }
    }

    public function render()
    {
        $query = Sale::with('client','user','store')
          ->where('tenant_id', Auth::user()->tenant_id);

        if ($this->store_id) {
            $query->where('store_id', $this->store_id);
        }

        // 🔍 Recherche par client
        if ($this->client_id) {
            $query->where('client_id', $this->client_id);
        }

        // 🔍 Statut
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // 🔍 Période
        if ($this->date_type == 'today') {
            $query->whereDate('sale_date', Carbon::today());
        } elseif ($this->date_type == 'month') {
            $query->whereMonth('sale_date', Carbon::now()->month)
                  ->whereYear('sale_date', Carbon::now()->year);
        } elseif ($this->date_type == 'year') {
            $query->whereYear('sale_date', Carbon::now()->year);
        } elseif ($this->date_type == 'range' && $this->start_date && $this->end_date) {
            $query->whereBetween('sale_date', [$this->start_date, $this->end_date]);
        }

        // 🔍 Recherche textuelle (par ID vente ou description)
        if ($this->search) {
            $query->where('id', $this->search);
        }

        $sales = $query->orderBy('sale_date','desc')->paginate(10);

        // 📊 Statistiques
        $total_sales = $query->count();
        $total_amount = $query->sum('total_amount');
        $total_paid = $query->sum('total_paid');
        $total_due = $total_amount - $total_paid;

        return view('livewire.reports.sales-report', [
            'sales' => $sales,
            'clients' => Client::where('tenant_id', Auth::user()->tenant_id)->get(),
            'total_sales' => $total_sales,
            'total_amount' => $total_amount,
            'total_paid' => $total_paid,
            'total_due' => $total_due,
        ]);
    }

    public function exportPdf()
    {
        $query = Sale::with('client','user','store')
          ->where('tenant_id', Auth::user()->tenant_id);

        if ($this->store_id) {
            $query->where('store_id', $this->store_id);
        }

        if ($this->client_id) $query->where('client_id', $this->client_id);
        if ($this->status) $query->where('status', $this->status);
        if ($this->date_type == 'today') $query->whereDate('sale_date', Carbon::today());
        elseif ($this->date_type == 'month') $query->whereMonth('sale_date', Carbon::now()->month)->whereYear('sale_date', Carbon::now()->year);
        elseif ($this->date_type == 'year') $query->whereYear('sale_date', Carbon::now()->year);
        elseif ($this->date_type == 'range' && $this->start_date && $this->end_date) $query->whereBetween('sale_date', [$this->start_date, $this->end_date]);

        $sales = $query->orderBy('sale_date','desc')->get();

        $pdf = Pdf::loadView('exports.sales-report', [
            'sales' => $sales,
            'store' => $this->store_id ? Store::find($this->store_id) : null,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, "rapport-ventes.pdf");
    }
}
