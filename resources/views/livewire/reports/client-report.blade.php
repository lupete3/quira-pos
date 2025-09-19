<div>
    <div class="d-flex justify-content-between mb-3">
        <div>
            <label>{{ __('Période :') }}</label>
            <input type="date" wire:model.lazy="filter_start" class="form-control d-inline w-auto">
            <input type="date" wire:model.lazy="filter_end" class="form-control d-inline w-auto">
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-bg-info">
                <div class="card-body">
                    <h5>{{ __('Total Facturé') }}</h5>
                    <h3>{{ number_format($total_factures, 2) }} {{ company()->devise }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-success">
                <div class="card-body">
                    <h5>{{ __('Total Payé') }}</h5>
                    <h3>{{ number_format($total_regles, 2) }} {{ company()->devise }}</h3>
                </div>
            </div>
        </div>
    </div>

    <h5>{{ __('Top 5 Clients') }}</h5>
    <ul class="list-group mb-4">
        @foreach($top_clients as $tc)
            <li class="list-group-item d-flex justify-content-between">
                <span>{{ $tc->client->name ?? __('Client supprimé') }}</span>
                <span><b>{{ number_format($tc->total_achats, 2) }} {{ company()->devise }}</b></span>
            </li>
        @endforeach
    </ul>

    <h5>{{ __('Liste des Clients') }}</h5>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>{{ __('Nom') }}</th>
                <th>{{ __('Téléphone') }}</th>
                <th>{{ __('Total Achats') }}</th>
                <th>{{ __('Total Payé') }}</th>
                <th>{{ __('Solde Dû') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
                @php
                    $total = $client->sales->sum('total_amount');
                    $paye = $client->sales->sum('total_paid');
                @endphp
                <tr>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->phone }}</td>
                    <td>{{ number_format($total, 2) }} {{ company()->devise }}</td>
                    <td>{{ number_format($paye, 2) }} {{ company()->devise }}</td>
                    <td class="{{ $total > $paye ? 'text-danger fw-bold' : 'text-success' }}">
                        {{ number_format($total - $paye, 2) }} {{ company()->devise }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $clients->links() }}
</div>
