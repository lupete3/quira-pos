
    

<x-layouts.app>
  <x-slot:title>
        {{ __('inventory_detail.details_inventaire') }} #{{ $inventory->id }}
  </x-slot:title>
  
  <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
          {{-- Clé : inventaire --}}
          <h5 class="card-title mb-0">{{ __('inventory_detail.inventaire') }} #{{ $inventory->id }}</h5>
          <div>
            <a href="{{ route('inventories.export', $inventory->id) }}" class="btn btn-danger" wire:click="exportPdf">
                <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                <i class="bx bx-download"></i> 
                {{-- Clé : exporter --}}
                {{ __('inventory_detail.exporter') }}
            </a>
            <a href="{{ route('inventories.index') }}" wire:navigate class="btn btn-secondary">
                {{-- Clé : retour_liste --}}
                {{ __('inventory_detail.retour_liste') }}
            </a>
          </div>
      </div>
      <div class="card-body">
          {{-- Informations Générales --}}
          <p><strong>{{ __('inventory_detail.date') }}:</strong> {{ $inventory->inventory_date }}</p>
          <p><strong>{{ __('inventory_detail.utilisateur') }}:</strong> {{ $inventory->user->name ?? __('inventory_detail.non_disponible') }}</p>
          <p><strong>{{ __('inventory_detail.magasin') }}:</strong> {{ $inventory->store->name ?? __('inventory_detail.non_disponible') }}</p>
          <p><strong>{{ __('inventory_detail.statut') }}:</strong> {{ $inventory->status }}</p>

          {{-- Tableau des Articles d'Inventaire --}}
          <div class="table-responsive text-nowrap mt-3">
              <table class="table table-hover">
                  <thead>
                      <tr>
                          <th>{{ __('inventory_detail.produit') }}</th>
                          <th>{{ __('inventory_detail.stock_theorique') }}</th>
                          <th>{{ __('inventory_detail.stock_physique') }}</th>
                          <th>{{ __('inventory_detail.difference') }}</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse($inventory->items as $item)
                          <tr>
                              <td>{{ $item->product->name ?? __('inventory_detail.non_disponible') }}</td>
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
                      @empty
                          <tr>
                              <td colspan="4" class="text-center">
                                  {{ __('inventory_detail.aucun_article') }}
                              </td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>
      </div>
  </div>

</x-layouts.app>

