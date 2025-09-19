<?php

namespace App\Livewire;

use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;

class UnitList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteConfirmed' => 'delete'];


    public $search = '';
    public $unitId;
    public $name;
    public $abbreviation;
    public $isEditMode = false;

    public function render()
    {
        $units = Unit::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('abbreviation', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.unit-list', [
            'units' => $units,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        $this->unitId = $id;
        $this->name = $unit->name;
        $this->abbreviation = $unit->abbreviation;
        $this->isEditMode = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:50|unique:units,name,' . $this->unitId,
            'abbreviation' => 'nullable|string|max:20',
        ];
        
        $this->validate($rules);

        Unit::updateOrCreate(
            ['id' => $this->unitId],
            [
                'name' => $this->name,
                'abbreviation' => $this->abbreviation,
            ]
        );

        notyf()->success($this->isEditMode ? 'Unit updated successfully.' : 'Unit created successfully.');

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->unitId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        Unit::find($this->unitId)->delete();
        notyf()->success('Unit deleted successfully.');
    }

    private function resetInputFields()
    {
        $this->unitId = null;
        $this->name = '';
        $this->abbreviation = '';
    }
}