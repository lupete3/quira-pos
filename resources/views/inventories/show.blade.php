<x-layouts.app>
    <x-slot:title>
        {{ __('Détails de l\'inventaire') }} #{{ $inventory->id }}
    </x-slot:title>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Inventaire') }} #{{ $inventory->id }}</h5>
            <div>
              <a href="{{ route('inventories.export', $inventory->id) }}" class="btn btn-danger" wire:click="exportPdf">
                  <i class="bx bx-download"></i> {{ __('Exporter') }}
              </a>
              <a href="{{ route('inventories.index') }}" wire:navigate class="btn btn-secondary">
                  {{ __('Retour à la liste') }}
              </a>
            </div>
        </div>
        <div class="card-body">
            <p><strong>{{ __('Date') }}:</strong> {{ $inventory->inventory_date }}</p>
            <p><strong>{{ __('Utilisateur') }}:</strong> {{ $inventory->user->name ?? __('N/A') }}</p>
            <p><strong>{{ __('Magasin') }}:</strong> {{ $inventory->store->name ?? __('N/A') }}</p>
            <p><strong>{{ __('Statut') }}:</strong> {{ $inventory->status }}</p>

            <div class="table-responsive text-nowrap mt-3">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Produit') }}</th>
                            <th>{{ __('Stock théorique') }}</th>
                            <th>{{ __('Stock physique') }}</th>
                            <th>{{ __('Différence') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventory->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? __('N/A') }}</td>
                                <td>{{ $item->theoretical_quantity }}</td>
                                <td>{{ $item->physical_quantity }}</td>
                                <td>
                                    @if($item->difference > 0)
                                        <span class="badge bg-label-success">+{{ $item->difference }}</span>
                                    @elseif($item->difference < 0)
                                        <span class="badge bg-label-danger">{{ $item->difference }}</span>
                                    @else
                                        {{ $item->difference }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
