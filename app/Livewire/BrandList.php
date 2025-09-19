<?php

namespace App\Livewire;

use App\Models\Brand;
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
        $brands = Brand::where('name', 'like', '%' . $this->search . '%')
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
            ['name' => $this->name]
        );

        notyf()->success($this->isEditMode ? 'Brand updated successfully.' : 'Brand created successfully.');

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
        notyf()->success('Brand deleted successfully.');
    }

    private function resetInputFields()
    {
        $this->brandId = null;
        $this->name = '';
    }
}
