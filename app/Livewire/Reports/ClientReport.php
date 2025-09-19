<?php

namespace App\Livewire\Reports;

use App\Models\Client;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class ClientReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $filter_start;
    public $filter_end;

    public function mount()
    {
        $this->filter_start = Carbon::now()->startOfMonth()->toDateString();
        $this->filter_end = Carbon::now()->endOfMonth()->toDateString();
    }

    public function render()
    {
        // Clients avec ventes filtrées par période
        $clients = Client::with(['sales' => function ($q) {
            $q->whereBetween('created_at', [$this->filter_start, $this->filter_end]);
        }])->paginate(10);

        // Totaux globaux
        $total_factures = Sale::whereBetween('created_at', [$this->filter_start, $this->filter_end])->sum('total_amount');
        $total_regles = Sale::whereBetween('created_at', [$this->filter_start, $this->filter_end])->sum('total_paid');

        // Top 5 clients
        $top_clients = Sale::selectRaw('client_id, SUM(total_amount) as total_achats')
            ->whereBetween('created_at', [$this->filter_start, $this->filter_end])
            ->groupBy('client_id')
            ->orderByDesc('total_achats')
            ->with('client')
            ->take(5)
            ->get();

        return view('livewire.reports.client-report', [
            'clients' => $clients,
            'total_factures' => $total_factures,
            'total_regles' => $total_regles,
            'top_clients' => $top_clients,
        ]);
    }
}

