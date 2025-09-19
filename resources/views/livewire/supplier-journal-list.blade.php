<div>
    {{-- Filtres --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <label for="supplier_id" class="form-label">{{ __('Sélectionner un fournisseur') }}</label>
            <select class="form-select" wire:model.live="supplier_id">
                <option value="">{{ __('Tous les fournisseurs') }}</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tableau du journal des fournisseurs --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Fournisseur') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Détails') }}</th>
                    <th>{{ __('Paiement') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($journalEntries as $entry)
                    <tr wire:key="{{ $entry->id }}">
                        <td>{{ $entry->entry_date }}</td>
                        <td><strong>{{ $entry->supplier->name ?? 'N/A' }}</strong></td>
                        <td>
                            @if($entry->purchase_id) <span class="badge bg-label-info">{{ __('Achat') }}</span>
                            @elseif($entry->purchase_return_id) <span class="badge bg-label-warning">{{ __('Retour') }}</span>
                            @elseif($entry->payment) <span class="badge bg-label-success">{{ __('Paiement') }}</span>
                            @endif
                        </td>
                        <td>{{ $entry->description }}</td>
                        <td>{{ $entry->payment ? number_format($entry->payment, 2) . ' ' . company()->devise : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">{{ __('Aucune entrée de journal trouvée.') }}</td>
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
