<?php

namespace App\Livewire;

use App\Models\CashRegister;
use App\Models\Store;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class StoreList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteConfirmed' => 'delete'];

    public $search = '';
    public $storeId;
    public $name;
    public $location;
    public $phone;
    public $email;
    public $users = [];
    public $selectedUsers = [];
    public $isEditMode = false;

    public $userRoles = []; // clé = userId, valeur = role

    public function render()
    {
        $stores = Store::with('users')
            ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        $allUsers = User::orderBy('name')->get();

        return view('livewire.store-list', [
            'stores' => $stores,
            'allUsers' => $allUsers,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $store = Store::with('users')->findOrFail($id);
        $this->storeId = $id;
        $this->name = $store->name;
        $this->location = $store->location;
        $this->phone = $store->phone;
        $this->email = $store->email;
        $this->selectedUsers = $store->users->pluck('id')->toArray();
        $this->userRoles = $store->users->mapWithKeys(fn($u) => [$u->id => $u->pivot->role])->toArray();
        $this->isEditMode = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:150|unique:stores,name,' . $this->storeId,
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
        ];

        $this->validate($rules);

        $store = Store::updateOrCreate(
            ['id' => $this->storeId],
            [
                'name' => $this->name,
                'location' => $this->location,
                'phone' => $this->phone,
                'email' => $this->email,
            ]
        );

        // ⚡ Création ou mise à jour de la caisse associée
        CashRegister::updateOrCreate(
            ['store_id' => $store->id],
            [
                'opening_balance' => $store->cashRegister->opening_balance ?? 0,
                'current_balance' => $store->cashRegister->current_balance ?? 0,
            ]
        );

        // Gestion des affectations utilisateurs
        $syncData = [];
        foreach ($this->selectedUsers as $userId) {
            $syncData[$userId] = ['role' => $this->userRoles[$userId] ?? 'stock_keeper'];
        }
        $store->users()->sync($syncData);

        notyf()->success($this->isEditMode ? 'Point de vente mis à jour.' : 'Point de vente créé.');

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->storeId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        $store = Store::findOrFail($this->storeId);
        $store->users()->detach();
        $store->delete();

        notyf()->success('Point de vente supprimé.');
    }

    private function resetInputFields()
    {
        $this->storeId = null;
        $this->name = '';
        $this->location = '';
        $this->phone = '';
        $this->email = '';
        $this->selectedUsers = [];
    }
}
