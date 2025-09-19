<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\ClientJournal;
use Livewire\Component;
use Livewire\WithPagination;

class ClientJournalList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $client_id;

    public function render()
    {
        $clients = Client::all();

        $journalEntries = ClientJournal::with('client')
            ->when($this->client_id, function ($query) {
                $query->where('client_id', $this->client_id);
            })
            ->latest('entry_date')
            ->paginate(10);

        return view('livewire.client-journal-list', compact('clients', 'journalEntries'));
    }

    public function updatingClientId()
    {
        $this->resetPage();
    }
}