<div>
    <!-- Filtres -->
    <div class="card mb-3">
        <div class="card-body row g-2">
          @if (Auth::user()->role_id == 1)
            <div class="col-md-2">
                <select class="form-select" wire:model.live="store_id">
                    <option value="">{{ __('Tous les magasins') }}</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
          @endif
            <div class="col-md-2">
                <select class="form-select" wire:model.lazy="client_id">
                    <option value="">{{ __('Tous les clients') }}</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" wire:model.lazy="status">
                    <option value="">{{ __('Tous statuts') }}</option>
                    <option value="validated">{{ __('Validées') }}</option>
                    <option value="pending">{{ __('En attente') }}</option>
                    <option value="returned">{{ __('Retournées') }}</option>
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
                <strong>{{ __('Nombre de ventes') }}</strong>
                <h5>{{ $total_sales }}</h5>
            </div>
        </div>
        <div class="col">
            <div class="card p-2 shadow-sm">
                <strong>{{ __('Total ventes') }}</strong>
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
                <strong>{{ __('Crédit restant') }}</strong>
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
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Montant Total') }}</th>
                        <th>{{ __('Payé') }}</th>
                        <th>{{ __('Restant') }}</th>
                        <th>{{ __('Magasin') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->id }}</td>
                            <td>{{ $sale->client?->name ?? __('Client Libre') }}</td>
                            <td>{{ number_format($sale->total_amount,2) }}</td>
                            <td class="text-success">{{ number_format($sale->total_paid,2) }}</td>
                            <td class="text-danger">{{ number_format($sale->total_amount - $sale->total_paid,2) }}</td>
                            <td>{{ $sale->store?->name ?? __('N/A') }}</td>
                            <td>{{ $sale->sale_date }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('Aucune vente trouvée') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $sales->links() }}
    </div>
</div>
