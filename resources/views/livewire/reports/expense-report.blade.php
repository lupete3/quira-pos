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
                <select class="form-select" wire:model.lazy="category_id">
                    <option value="">{{ __('Toutes catégories') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
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
        <div class="col">
            <div class="card p-2 shadow-sm">
                <strong>{{ __('Nombre de dépenses') }}</strong>
                <h5>{{ $total_expenses }}</h5>
            </div>
        </div>
        <div class="col">
            <div class="card p-2 shadow-sm">
                <strong>{{ __('Montant total') }}</strong>
                <h5 class="text-danger">{{ number_format($total_amount,2) }} {{ company()->devise }}</h5>
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
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Montant') }}</th>
                        <th>{{ __('Catégorie') }}</th>
                        <th>{{ __('Magasin') }}</th>
                        <th>{{ __('Utilisateur') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $exp)
                        <tr>
                            <td>{{ $exp->id }}</td>
                            <td>{{ $exp->description }}</td>
                            <td class="text-danger">{{ number_format($exp->amount,2) }} {{ company()->devise }}</td>
                            <td>{{ $exp->category?->name ?? '-' }}</td>
                            <td>{{ $exp->store?->name ?? '-' }}</td>
                            <td>{{ $exp->user?->name ?? '-' }}</td>
                            <td>{{ $exp->expense_date }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('Aucune dépense trouvée') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $expenses->links() }}
    </div>
</div>
