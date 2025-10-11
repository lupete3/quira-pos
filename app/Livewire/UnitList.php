<?php

namespace App\Livewire;

use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
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
        $tenantId = Auth::user()->tenant_id;

        $units = Unit::where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('abbreviation', 'like', '%' . $this->search . '%');
            })
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
      $tenantId = Auth::user()->tenant_id;

        $rules = [
            'name' => 'required|string|max:50|unique:units,name,' . $this->unitId . ',id,tenant_id,' . $tenantId,
            'abbreviation' => 'nullable|string|max:20',
        ];

        $this->validate($rules);

        Unit::updateOrCreate(
            ['id' => $this->unitId],
            [
                'tenant_id' => Auth::user()->tenant_id,
                'name' => $this->name,
                'abbreviation' => $this->abbreviation,
            ]
        );

        notyf()->success(__($this->isEditMode ? 'Unité de mesure mise à jour avec succès' : 'Unité de mesure créée avec succès'));

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
        notyf()->success(__('Unité de mesure a été supprimée avec succès'));
    }

    private function resetInputFields()
    {
        $this->unitId = null;
        $this->name = '';
        $this->abbreviation = '';
    }
}
