<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RawMaterialTransaction;
use App\Models\Store;
use App\Models\RawMaterial;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;

class RawMaterialReport extends Component
{
    use WithPagination;

    // ...

    protected $paginationTheme = 'bootstrap';

    public $store_id = '';
    public $raw_material_id = '';
    public $date_from;
    public $date_to;

    private function getUserStores($tenantId)
    {
        $user = Auth::user();

        if ($user->role_id == 1) {
            return Store::where('tenant_id', $tenantId)->get();
        }

        return $user->stores()->where('tenant_id', $tenantId)->get();
    }

    public function export()
    {
        $tenantId = Auth::user()->tenant_id;
        $userStores = $this->getUserStores($tenantId);
        $storeIds = $userStores->pluck('id');

        $transactions = RawMaterialTransaction::with(['store', 'rawMaterial', 'user'])
            ->where('tenant_id', $tenantId)
            ->whereIn('store_id', $storeIds)
            ->when($this->store_id, function ($query) {
                $query->where('store_id', $this->store_id);
            })
            ->when($this->raw_material_id, function ($query) {
                $query->where('raw_material_id', $this->raw_material_id);
            })
            ->when($this->date_from, function ($query) {
                $query->whereDate('created_at', '>=', $this->date_from);
            })
            ->when($this->date_to, function ($query) {
                $query->whereDate('created_at', '<=', $this->date_to);
            })
            ->latest()
            ->get();

        $pdf = Pdf::loadView('exports.raw-materials-report', [
            'transactions' => $transactions
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "rapport-mouvements-mp.pdf");
    }

    public function render()
    {
        $tenantId = Auth::user()->tenant_id;
        $userStores = $this->getUserStores($tenantId);
        $storeIds = $userStores->pluck('id');

        $transactions = RawMaterialTransaction::with(['store', 'rawMaterial', 'user'])
            ->where('tenant_id', $tenantId)
            ->whereIn('store_id', $storeIds)
            ->when($this->store_id, function ($query) {
                $query->where('store_id', $this->store_id);
            })
            ->when($this->raw_material_id, function ($query) {
                $query->where('raw_material_id', $this->raw_material_id);
            })
            ->when($this->date_from, function ($query) {
                $query->whereDate('created_at', '>=', $this->date_from);
            })
            ->when($this->date_to, function ($query) {
                $query->whereDate('created_at', '<=', $this->date_to);
            })
            ->latest()
            ->paginate(20);

        return view('livewire.raw-material-report', [
            'transactions' => $transactions,
            'stores' => $userStores,
            'rawMaterials' => RawMaterial::where('tenant_id', $tenantId)->get(),
        ]);
    }
}
