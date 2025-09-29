<?php

namespace App\Livewire;

use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class RoleList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteConfirmed' => 'delete'];

    public $search = '';
    public $roleId;
    public $name;
    public $description;
    public $isEditMode = false;

    public function render()
    {
        $roles = Role::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.role-list', compact('roles'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->roleId = $id;
        $this->name = $role->name;
        $this->description = $role->description;
        $this->isEditMode = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:50|unique:roles,name,' . $this->roleId,
            'description' => 'nullable|string',
        ];
        
        $this->validate($rules);

        Role::updateOrCreate(
            ['id' => $this->roleId],
            [
                'name' => $this->name,
                'description' => $this->description,
            ]
        );

        notyf()->success(__($this->isEditMode ? 'Rôle mise à jour avec succès.' : 'Rôle crée avec succès.'));

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->roleId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        Role::find($this->roleId)->delete();
        notyf()->success(__('Rôle supprimé avec succès.'));
    }

    private function resetInputFields()
    {
        $this->roleId = null;
        $this->name = '';
        $this->description = '';
    }
}