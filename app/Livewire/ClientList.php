<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ClientList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteConfirmed' => 'delete'];

    public $search = '';
    public $clientId;
    public $name, $email, $phone, $address, $debt;
    public $isEditMode = false;

    public function render()
    {
        $tenantId = Auth::user()->tenant_id;

        $clients = Client::where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.client-list', compact('clients'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        $this->clientId = $id;
        $this->name = $client->name;
        $this->email = $client->email;
        $this->phone = $client->phone;
        $this->address = $client->address;
        $this->debt = $client->debt;
        $this->isEditMode = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'debt' => 'nullable|numeric|min:0',
        ];
        
        $this->validate($rules);

        Client::updateOrCreate(
            ['id' => $this->clientId],
            [
                'tenant_id' => Auth::user()->tenant_id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'debt' => $this->debt ?? 0,
            ]
        );

        notyf()->success(__($this->isEditMode ? 'Client mis à jour avec succès.' : 'Client crée avec succès.'));

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->clientId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        Client::find($this->clientId)->delete();
        notyf()->success(__('Client supprimé avec succès'));
    }

    private function resetInputFields()
    {
        $this->clientId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->debt = 0;
    }
}