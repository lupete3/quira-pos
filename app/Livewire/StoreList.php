<?php

namespace App\Livewire;

use App\Models\CashRegister;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
        $tenantId = Auth::user()->tenant_id;

        $stores = Store::with('users')
            ->where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        $allUsers = User::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return view('livewire.store-list', compact('stores', 'allUsers'));
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
        $user = Auth::user();
        $tenant = $user->tenant;

        // ⚡ Récupérer la souscription active
        $subscription = $tenant->subscriptions()
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->latest()
            ->first();

        if (!$subscription) {
            notyf()->error(__('store.erreur_point_vente'));
            return;
        }

        $plan = $subscription->plan;

        // Vérifier la limite des magasins (si le plan n'est pas illimité)
        if (!empty($plan->max_stores) && $plan->max_stores > 0) {
            $currentStoresCount = $tenant->stores()->count();

            // Seulement si on crée un nouveau magasin (pas en édition)
            if (!$this->storeId && $currentStoresCount >= $plan->max_stores) {
                notyf()->error(__('store.erreur_point_vente') . " ({$plan->max_stores})");
                return;
            }
        }

        $rules = [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('stores', 'name')
                    ->ignore($this->storeId)
                    ->where(fn($q) => $q->where('tenant_id', $tenant->id)),
            ],
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
        ];

        $this->validate($rules, [
            'name.required' => __('store.nom_requis'),
            'email.email' => __('store.email_invalide'),
        ]);

        $store = Store::updateOrCreate(
            ['id' => $this->storeId],
            [
                'name' => $this->name,
                'location' => $this->location,
                'phone' => $this->phone,
                'email' => $this->email,
                'tenant_id' => $tenant->id,
            ]
        );

        // ⚡ Création ou mise à jour de la caisse associée
        CashRegister::updateOrCreate(
            ['store_id' => $store->id],
            [
                'tenant_id' => $tenant->id,
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

        // Notification
        notyf()->success($this->isEditMode ? __('store.point_vente_modifie') : __('store.point_vente_cree'));

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

        notyf()->success(__('store.point_vente_supprime'));
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
