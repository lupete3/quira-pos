<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
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
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        if (Auth::user()->role_id != 4) {
            $roles = Role::where('name', '=', 'Admin')->orWhere('name', '=', 'Gérant')->orWhere('name', '=', 'Caissier')->get();
        }else{
          $roles = Role::where('name', '=', 'Super Admin')->get();
        }

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
        $tenant = Auth::user()->tenant;

        // ⚡ Récupérer la souscription active
        $subscription = $tenant->subscriptions()
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->latest()
            ->first();

        if (!$subscription) {
            notyf()->error(__('Aucune souscription active.'));
            return;
        }

        $plan = $subscription->plan;

        // Vérifier la limite des utilisateurs (si le plan n'est pas illimité)
        if (!empty($plan->max_users) && $plan->max_users > 0) {
            $currentUsersCount = $tenant->users()->count();

            // Seulement si on crée un nouvel utilisateur (pas en édition)
            if (!$this->userId && $currentUsersCount >= $plan->max_users) {
                notyf()->error(__('La limite de nombre d\'utilisateurs a été atteinte.'));
                return;
            }
        }

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
            'tenant_id' => $tenant->id,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        User::updateOrCreate(['id' => $this->userId], $data);

        notyf()->success(__($this->isEditMode ? 'Utilisateur mis à jour.' : 'Utilisateur créé avec succès.'));

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
        notyf()->success(__('Utilisateur supprimé avec succès.'));
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
