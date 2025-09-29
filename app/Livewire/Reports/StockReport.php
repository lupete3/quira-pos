<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class StockReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $store_id;
    public $stores = [];

    public function mount()
    {
        $this->stores = Store::all();

        if (Auth::user()->role_id != 1) {
          $store = Auth::user()->stores()->first();
          $this->store_id = $store?->id; // sécurisation si pas de magasin
        }
    }

    public function render()
    {
        $stores = Store::where('tenant_id', Auth::user()->tenant_id)->get();

        $query = Product::query()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('name', 'like', "%{$this->search}%")
            ->with('category');

        // si un store est choisi
        if ($this->store_id) {
            $query->whereHas('stores', function ($q) {
                $q->where('store_id', $this->store_id);
            })->with(['stores' => function ($q) {
                $q->where('store_id', $this->store_id);
            }]);
        } else {
            $query->with('stores');
        }

        $products = $query->paginate($this->perPage);

        return view('livewire.reports.stock-report', [
            'products' => $products,
            'stores'   => $stores,
        ]);
    }

    public function exportPDF()
    {
        $query = Product::query()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('name', 'like', "%{$this->search}%")
            ->with('category');

        if ($this->store_id) {
            $query->whereHas('stores', function ($q) {
                $q->where('store_id', $this->store_id);
            })->with(['stores' => function ($q) {
                $q->where('store_id', $this->store_id);
            }]);
        } else {
            $query->with('stores');
        }

        $products = $query->get();

        $pdf = Pdf::loadView('exports.stock-report-pdf', [
            'products' => $products,
            'store'    => $this->store_id ? Store::find($this->store_id) : null,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'rapport-stock.pdf');
    }
}
