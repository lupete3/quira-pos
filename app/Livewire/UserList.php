<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteConfirmed' => 'delete'];

    public $search = '';
    public $userId;
    public $name, $email, $role_id, $password, $password_confirmation;
    public $isEditMode = false;

    public function render()
    {
        $users = User::with('role')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);
        
        $roles = Role::all();

        return view('livewire.user-list', compact('users', 'roles'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        $this->isEditMode = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->userId,
            'role_id' => 'required|exists:roles,id',
            'password' => $this->isEditMode ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
        ];
        
        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }


        User::updateOrCreate(['id' => $this->userId], $data);

        notyf()->success($this->isEditMode ? 'User updated successfully.' : 'User created successfully.');

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->userId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        User::find($this->userId)->delete();
        notyf()->success('User deleted successfully.');
    }

    private function resetInputFields()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->role_id = '';
        $this->password = '';
        $this->password_confirmation = '';
    }
}