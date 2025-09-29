<?php

namespace App\Livewire;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BrandList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteConfirmed' => 'delete'];

    public $search = '';
    public $brandId;
    public $name;
    public $isEditMode = false;

    public function render()
    {
        $brands = Brand::where('tenant_id', Auth::user()->tenant_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.brand-list', [
            'brands' => $brands,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        $this->brandId = $id;
        $this->name = $brand->name;
        $this->isEditMode = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:100|unique:brands,name,' . $this->brandId,
        ];

        $this->validate($rules);

        Brand::updateOrCreate(
            ['id' => $this->brandId],
            [
              'tenant_id' => Auth::user()->tenant_id,
              'name' => $this->name
            ]
        );

        notyf()->success(__($this->isEditMode ? 'Marque mise à jour avec succès.' : 'Marque ajoutée avec succès.'));

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->brandId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        Brand::find($this->brandId)->delete();
        notyf()->success(__('Marque supprimée avec succès'));
    }

    private function resetInputFields()
    {
        $this->brandId = null;
        $this->name = '';
    }
}
