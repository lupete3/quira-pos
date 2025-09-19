<div>
    {{-- Filtres --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="start_date" class="form-label">{{ __('Date de début') }}</label>
            <input type="date" class="form-control" wire:model.live="start_date">
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label">{{ __('Date de fin') }}</label>
            <input type="date" class="form-control" wire:model.live="end_date">
        </div>
    </div>

    {{-- Tableau du rapport des achats --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Fournisseur') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Montant total') }}</th>
                    <th>{{ __('Montant payé') }}</th>
                    <th>{{ __('Statut') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($purchases as $purchase)
                    <tr wire:key="{{ $purchase->id }}">
                        <td>{{ $purchase->id }}</td>
                        <td>{{ $purchase->supplier->name ?? __('N/A') }}</td>
                        <td>{{ $purchase->purchase_date }}</td>
                        <td>{{ number_format($purchase->total_amount, 2) }} {{ company()->devise }}</td>
                        <td>{{ number_format($purchase->total_paid, 2) }} {{ company()->devise }}</td>
                        <td><span class="badge bg-label-primary me-1">{{ $purchase->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ __('Aucun achat trouvé pour la période sélectionnée.') }}</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">{{ __('Total :') }}</th>
                    <th>{{ number_format($total_amount, 2) }} {{ company()->devise }}</th>
                    <th>{{ number_format($total_paid, 2) }} {{ company()->devise }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $purchases->links() }}
    </div>
</div>
