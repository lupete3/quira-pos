<div>
    <!-- Filtres -->
    <div class="card mb-3">
        <div class="card-body row g-2">
            <div class="col-md-2">
                <select class="form-select" wire:model.lazy="supplier_id">
                    <option value="">{{ __('Tous les fournisseurs') }}</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" wire:model.lazy="status">
                    <option value="">{{ __('Tous statuts') }}</option>
                    <option value="validated">{{ __('Validés') }}</option>
                    <option value="pending">{{ __('En attente') }}</option>
                    <option value="returned">{{ __('Retournés') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" wire:model.lazy="date_type">
                    <option value="all">{{ __('Toutes périodes') }}</option>
                    <option value="today">{{ __("Aujourd'hui") }}</option>
                    <option value="month">{{ __('Ce mois') }}</option>
                    <option value="year">{{ __('Cette année') }}</option>
                    <option value="range">{{ __('Intervalle') }}</option>
                </select>
            </div>
            @if($date_type == 'range')
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model="start_date">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model="end_date">
                </div>
            @endif
            <div class="col-md-2">
                <button class="btn btn-danger w-100" wire:click="exportPdf" wire:target="exportPdf" wire:loading.attr="disabled">
                  <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                  <i class="bx bx-download"></i> {{ __('Exporter PDF') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row text-center mb-3">
        <div class="col">
            <div class="card p-2 shadow-sm">
                <strong>{{ __("Nombre d'achats") }}</strong>
                <h5>{{ $total_purchases }}</h5>
            </div>
        </div>
        <div class="col">
            <div class="card p-2 shadow-sm">
                <strong>{{ __('Total achats') }}</strong>
                <h5>{{ number_format($total_amount,2) }} {{ company()?->devise }}</h5>
            </div>
        </div>
        <div class="col">
            <div class="card p-2 shadow-sm">
                <strong>{{ __('Total payé') }}</strong>
                <h5 class="text-success">{{ number_format($total_paid,2) }} {{ company()?->devise }}</h5>
            </div>
        </div>
        <div class="col">
            <div class="card p-2 shadow-sm">
                <strong>{{ __('Dette fournisseur') }}</strong>
                <h5 class="text-danger">{{ number_format($total_due,2) }} {{ company()?->devise }}</h5>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Fournisseur') }}</th>
                        <th>{{ __('Montant Total') }}</th>
                        <th>{{ __('Payé') }}</th>
                        <th>{{ __('Restant') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->id }}</td>
                            <td>{{ $purchase->supplier?->name }}</td>
                            <td>{{ number_format($purchase->total_amount,2) }}</td>
                            <td class="text-success">{{ number_format($purchase->total_paid,2) }}</td>
                            <td class="text-danger">{{ number_format($purchase->total_amount - $purchase->total_paid,2) }}</td>
                            <td>
                                <span class="badge
                                    @if($purchase->status=='validated') bg-success
                                    @elseif($purchase->status=='pending') bg-warning
                                    @else bg-danger @endif">
                                    {{ __(ucfirst($purchase->status)) }}
                                </span>
                            </td>
                            <td>{{ $purchase->purchase_date }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('Aucun achat trouvé') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $purchases->links() }}
    </div>
</div>
