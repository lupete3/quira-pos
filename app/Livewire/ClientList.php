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
            'email' => 'nullable|email|max:100|unique:clients,email,' . $this->clientId,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'debt' => 'nullable|numeric|min:0',
        ];

        // Messages de validation traduits
        $messages = [
            'name.required' => __('client.nom_requis'),
            'email.unique' => __('client.email_unique'),
            // Note: Les règles 'email', 'max', 'min', etc. utilisent les messages par défaut de Laravel
            // sauf si vous les définissez explicitement ici. Pour l'exemple, nous nous concentrons sur les clés fournies.
        ];

        $this->validate($rules, $messages);

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

        // Notification de succès traduite
        $messageKey = $this->isEditMode ? 'client.client_mis_a_jour' : 'client.client_cree';
        notyf()->success(__($messageKey));

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
        try {
            Client::find($this->clientId)->delete();
            // Notification de suppression traduite
            notyf()->success(__('client.client_supprime'));
        } catch (\Exception $e) {
            // Gestion d'erreur (ex: clé étrangère) avec message traduit
            notyf()->error(__('client.erreur_client'));
        }
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
