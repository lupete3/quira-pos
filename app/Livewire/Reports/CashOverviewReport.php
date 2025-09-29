<?php

namespace App\Livewire\Reports;

use App\Models\CashRegister;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CashTransaction;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CashOverviewReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $store_id = '';
    public $date_type = 'all'; // all, today, month, year, range
    public $start_date = '';
    public $end_date = '';

    public $stores = [];

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
        $this->stores = Store::where('tenant_id', Auth::user()->tenant_id)->get();
    }

    public function render()
    {
        $query = CashTransaction::with('cashRegister.store','user')
          ->where('tenant_id', Auth::user()->tenant_id);

        if ($this->store_id) {
            $query->whereHas('cashRegister', fn($q) => $q->where('store_id', $this->store_id));
        }

        if ($this->date_type == 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->date_type == 'month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        } elseif ($this->date_type == 'year') {
            $query->whereYear('created_at', Carbon::now()->year);
        } elseif ($this->date_type == 'range' && $this->start_date && $this->end_date) {
            $query->whereBetween('created_at', [$this->start_date, $this->end_date]);
        }

        if ($this->search) {
            $query->where('description', 'like', "%{$this->search}%");
        }

        $transactions = $query->orderBy('created_at','desc')->paginate(10);

        // Statistiques
        $total_in = (clone $query)->where('type', 'in')->sum('amount');
        $total_out = (clone $query)->where('type', 'out')->sum('amount');
        $net_balance = $total_in - $total_out;

        // 🔹 Solde actuel caisse
        $current_balance = null;
        $store = null;

        if ($this->store_id) {
            $store = Store::find($this->store_id);
            if ($store) {
                $cashRegister = CashRegister::where('tenant_id', Auth::user()->tenant_id)->where('store_id', $store->id)->first();
                $current_balance = $cashRegister?->current_balance ?? 0;
            }
        }else {
            $current_balance = CashRegister::where('tenant_id', Auth::user()->tenant_id)->get()->sum('current_balance'); // Prendre la première caisse trouvée
        }

        return view('livewire.reports.cash-overview-report', [
            'transactions'    => $transactions,
            'total_in'        => $total_in,
            'total_out'       => $total_out,
            'net_balance'     => $net_balance,
            'current_balance' => $current_balance,
            'stores'          => $this->stores,
            'store'           => $store,
        ]);
    }

    public function exportPdf()
    {
        $query = CashTransaction::with('cashRegister.store','user')
          ->where('tenant_id', Auth::user()->tenant_id);

        if ($this->store_id) {
            $query->whereHas('cashRegister', fn($q) => $q->where('store_id', $this->store_id));
        }

        if ($this->date_type == 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->date_type == 'month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        } elseif ($this->date_type == 'year') {
            $query->whereYear('created_at', Carbon::now()->year);
        } elseif ($this->date_type == 'range' && $this->start_date && $this->end_date) {
            $query->whereBetween('created_at', [$this->start_date, $this->end_date]);
        }

        if ($this->search) {
            $query->where('description', 'like', "%{$this->search}%");
        }

        $transactions = $query->orderBy('created_at','desc')->get();

        // 🔹 Statistiques identiques à render()
        $total_in = (clone $query)->where('type', 'in')->sum('amount');
        $total_out = (clone $query)->where('type', 'out')->sum('amount');
        $net_balance = $total_in - $total_out;

        // 🔹 Solde réel de la caisse
        $store = $this->store_id ? Store::find($this->store_id) : null;
        $current_balance = null;

        if ($store) {
            $cashRegister = CashRegister::where('store_id', $store->id)->first();
            $current_balance = $cashRegister?->current_balance ?? 0;
        }else {
            $current_balance = CashRegister::where('tenant_id', Auth::user()->tenant_id)->get()->sum('current_balance'); // Prendre la première caisse trouvée
        }

        $pdf = Pdf::loadView('exports.cash-overview-report', [
            'transactions'    => $transactions,
            'store'           => $store,
            'start_date'      => $this->start_date,
            'end_date'        => $this->end_date,
            'total_in'        => $total_in,
            'total_out'       => $total_out,
            'net_balance'     => $net_balance,
            'current_balance' => $current_balance,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, "rapport-caisse.pdf");
    }

}

