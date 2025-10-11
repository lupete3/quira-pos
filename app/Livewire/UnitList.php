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
        // 1. Définition des règles
        $rules = [
            'name' => 'required|string|max:50',
            'abbreviation' => 'nullable|string|max:20',
        ];

        // 2. Définition des messages d'erreur de validation traduits
        $messages = [
            'name.required' => __('unit.nom_requis'),
            'name.unique' => __('unit.nom_unique'),
            // Vous pouvez ajouter plus de messages ici si nécessaire
            // 'name.max' => __('unit.nom_trop_long'),
        ];

        // 3. Validation avec messages traduits
        $this->validate($rules, $messages);

        Unit::updateOrCreate(
            ['id' => $this->unitId],
            [
                'tenant_id' => Auth::user()->tenant_id,
                'name' => $this->name,
                'abbreviation' => $this->abbreviation,
            ]
        );

        // 4. Notification avec message traduit
        $messageKey = $this->isEditMode ? 'unit.unite_mise_a_jour' : 'unit.unite_creee';
        notyf()->success(__($messageKey));

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
        try {
            Unit::find($this->unitId)->delete();
            // 5. Notification de suppression traduite
            notyf()->success(__('unit.unite_supprimee'));
        } catch (\Exception $e) {
            // Gestion des erreurs (ex: clé étrangère) avec message traduit
            notyf()->error(__('unit.erreur_unite'));
        }
    }

    private function resetInputFields()
    {
        $this->unitId = null;
        $this->name = '';
        $this->abbreviation = '';
    }
}
