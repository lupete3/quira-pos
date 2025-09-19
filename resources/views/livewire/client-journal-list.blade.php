<div>
    {{-- Filtre par client --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <label for="client_id" class="form-label">{{ __('Sélectionner un client') }}</label>
            <select class="form-select" wire:model.live="client_id">
                <option value="">{{ __('Tous les clients') }}</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tableau du journal client --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Client') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Détails') }}</th>
                    <th>{{ __('Paiement') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($journalEntries as $entry)
                    <tr wire:key="{{ $entry->id }}">
                        <td>{{ $entry->entry_date }}</td>
                        <td><strong>{{ $entry->client->name ?? 'N/A' }}</strong></td>
                        <td>
                            @if($entry->sale_id)
                                <span class="badge bg-label-info">{{ __('Vente') }}</span>
                            @elseif($entry->sale_return_id)
                                <span class="badge bg-label-warning">{{ __('Retour') }}</span>
                            @elseif($entry->payment)
                                <span class="badge bg-label-success">{{ __('Paiement') }}</span>
                            @endif
                        </td>
                        <td>{{ $entry->description }}</td>
                        <td>{{ $entry->payment ? number_format($entry->payment, 2) : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">{{ __('Aucune entrée trouvée.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $journalEntries->links() }}
    </div>
</div>
