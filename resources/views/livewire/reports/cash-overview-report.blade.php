<div>
    <!-- Filtres -->
    <div class="card mb-3">
        <div class="card-body row g-2">
            <div class="col-md-2">
                <select class="form-select" wire:model.lazy="store_id">
                    <option value="">{{ __('Tous les magasins') }}</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
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
                    <input type="date" class="form-control" wire:model.lazy="start_date">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.lazy="end_date">
                </div>
            @endif
            <div class="col-md-2">
                <input type="text" class="form-control" wire:model.live="search" placeholder="{{ __('Rechercher...') }}">
            </div>
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
        <div class="col-md-3 mb-2">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-success">{{ __('Total Entrées') }}</h6>
                    <h4>{{ number_format($total_in, 2) }} {{ company()?->devise }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-danger">{{ __('Total Dépenses') }}</h6>
                    <h4>{{ number_format($total_out, 2) }} {{ company()?->devise }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-primary">{{ __('Solde Net') }}</h6>
                    <h4>{{ number_format($net_balance, 2) }} {{ company()?->devise }}</h4>
                </div>
            </div>
        </div>
        @if($current_balance !== null)
        <div class="col-md-3 mb-2">
            <div class="card border-dark">
                <div class="card-body">
                    <h6 class="text-dark">{{ __('Solde Actuel en Caisse') }}</h6>
                    <h4>{{ number_format($current_balance, 2) }} {{ company()?->devise }}</h4>
                </div>
            </div>
        </div>
        @endif
    </div>


    <!-- Tableau -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Montant') }}</th>
                        <th>{{ __('Magasin') }}</th>
                        <th>{{ __('Utilisateur') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tr)
                        <tr>
                            <td>{{ $tr->id }}</td>
                            <td>
                                @if($tr->type == 'in')
                                    <span class="badge bg-success">Entrée</span>
                                @else
                                    <span class="badge bg-danger">Sortie</span>
                                @endif
                            </td>
                            <td class="{{ $tr->type == 'in' ? 'text-success' : 'text-danger' }}">
                                {{ number_format($tr->amount,2) }} {{ company()?->devise }}
                            </td>
                            <td>{{ $tr->cashRegister?->store?->name ?? '-' }}</td>
                            <td>{{ $tr->user?->name ?? '-' }}</td>
                            <td>{{ $tr->description }}</td>
                            <td>{{ $tr->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('Aucune transaction trouvée') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-2">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
