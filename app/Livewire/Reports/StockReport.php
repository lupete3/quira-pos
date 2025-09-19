<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;

class StockReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $store_id;

    public function render()
    {
        $stores = Store::all();

        $query = Product::query()
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
