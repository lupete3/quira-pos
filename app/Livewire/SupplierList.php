<?php

namespace App\Livewire;

use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteConfirmed' => 'delete'];

    public $search = '';
    public $supplierId;
    public $name, $email, $phone, $address, $debt;
    public $isEditMode = false;

    public function render()
    {
        $suppliers = Supplier::where('tenant_id', Auth::user()->tenant_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orWhere('phone', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.supplier-list', compact('suppliers'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        $this->supplierId = $id;
        $this->name = $supplier->name;
        $this->email = $supplier->email;
        $this->phone = $supplier->phone;
        $this->address = $supplier->address;
        $this->debt = $supplier->debt;
        $this->isEditMode = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:100|unique:suppliers,email,' . $this->supplierId,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'debt' => 'nullable|numeric|min:0',
        ];
        
        $this->validate($rules);

        Supplier::updateOrCreate(
            ['id' => $this->supplierId],
            [
                'tenant_id' => Auth::user()->tenant_id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'debt' => $this->debt ?? 0,
            ]
        );

        notyf()->success(__($this->isEditMode ? 'Fournisseur mis à jour avec succès.' : 'Fournisseur créé avec succès.'));

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->supplierId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        Supplier::find($this->supplierId)->delete();
        notyf()->success(__('Le fournisseur a été supprimé avec succès.'));
    }

    private function resetInputFields()
    {
        $this->supplierId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->debt = 0;
    }
}