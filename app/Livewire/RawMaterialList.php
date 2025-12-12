<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RawMaterial;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;

class RawMaterialList extends Component
{
    use WithPagination;

    // ... (existing properties)

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteConfirmed' => 'delete', 'refreshList' => '$refresh'];

    public $search = '';
    public $rawMaterialId;
    public $designation;
    public $purchase_price;
    public $min_stock;

    public $isEditMode = false;

    private function getUserStores($tenantId)
    {
        $user = Auth::user();

        // Si Admin ou ou Super Admin, tous les magasins du tenant
        if ($user->role_id == 1) {
            return Store::where('tenant_id', $tenantId)->get();
        }

        // Sinon, seulement les magasins affectés
        return $user->stores()->where('tenant_id', $tenantId)->get();
    }

    public function export()
    {
        $tenant = $this->tenantId();
        $userStores = $this->getUserStores($tenant);
        $storeIds = $userStores->pluck('id');

        $rawMaterials = RawMaterial::with([
            'stores' => function ($query) use ($storeIds) {
                // Filter eager loaded stores to only show stock for accessible stores
                $query->whereIn('stores.id', $storeIds);
            }
        ])
            ->where('tenant_id', $tenant)
            ->when($this->search, function ($query) {
                $query->where('designation', 'like', "%{$this->search}%");
            })
            ->get();

        $pdf = Pdf::loadView('exports.raw-materials-list', [
            'rawMaterials' => $rawMaterials
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "matieres-premieres.pdf");
    }

    private function tenantId()
    {
        return Auth::user()->tenant_id;
    }

    public function render()
    {
        $tenant = $this->tenantId();
        $userStores = $this->getUserStores($tenant);
        $storeIds = $userStores->pluck('id');

        $rawMaterials = RawMaterial::with([
            'stores' => function ($query) use ($storeIds) {
                $query->whereIn('stores.id', $storeIds);
            }
        ])
            ->where('tenant_id', $tenant)
            ->when($this->search, function ($query) {
                $query->where('designation', 'like', "%{$this->search}%");
            })
            ->paginate(10);

        return view('livewire.raw-material-list', [
            'rawMaterials' => $rawMaterials,
            'stores' => $userStores,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $rawMaterial = RawMaterial::findOrFail($id);
        $this->isEditMode = true;
        $this->rawMaterialId = $id;

        $this->designation = $rawMaterial->designation;
        $this->purchase_price = $rawMaterial->purchase_price;
        $this->min_stock = $rawMaterial->min_stock;
    }

    public function save()
    {
        $tenant = $this->tenantId();

        $this->validate([
            'designation' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
        ]);

        RawMaterial::updateOrCreate(
            ['id' => $this->rawMaterialId],
            [
                'tenant_id' => $tenant,
                'designation' => $this->designation,
                'purchase_price' => $this->purchase_price,
                'min_stock' => $this->min_stock ?? 0,
            ]
        );

        notyf()->success(__($this->isEditMode ? 'raw_material.update' : 'raw_material.save'));

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->rawMaterialId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        try {
            RawMaterial::find($this->rawMaterialId)->delete();
            notyf()->success(__('raw_material.delete'));
        } catch (\Exception $e) {
            notyf()->error(__('raw_material.not_found'));
        }
    }

    private function resetInputFields()
    {
        $this->reset(['rawMaterialId', 'designation', 'purchase_price', 'min_stock']);
    }

}
