<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $date_from;
    public $date_to;

    public function render()
    {
        $query = Supplier::query()
            ->with(['purchases' => function ($q) {
                if ($this->date_from) {
                    $q->whereDate('created_at', '>=', $this->date_from);
                }
                if ($this->date_to) {
                    $q->whereDate('created_at', '<=', $this->date_to);
                }
            }]);

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $suppliers = $query->paginate(10);

        return view('livewire.reports.supplier-report', [
            'suppliers' => $suppliers
        ]);
    }

    public function exportPdf()
    {
        $query = Supplier::query()
            ->with(['purchases' => function ($q) {
                if ($this->date_from) {
                    $q->whereDate('created_at', '>=', $this->date_from);
                }
                if ($this->date_to) {
                    $q->whereDate('created_at', '<=', $this->date_to);
                }
            }]);

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $suppliers = $query->get();

        $pdf = Pdf::loadView('exports.supplier-report-pdf', [
            'suppliers' => $suppliers,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "rapport-fournisseurs.pdf"
        );
    }
}

