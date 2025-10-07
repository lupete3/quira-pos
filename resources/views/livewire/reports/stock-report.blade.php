<div>
    <div class="row mb-3">
        <div class="col-12 col-md-3 mb-2">
            <div class="input-group">
              @if (Auth::user()->role_id == 1)
                <select wire:model.lazy="store_id" class="form-select">
                    <option value="">{{ __('-- Tous les magasins --') }}</option>
                    @foreach($stores as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select>
              @endif
            </div>
        </div>
        <div class="col-12 col-md-6 mb-2">
            <input type="text" wire:model.live="search" class="form-control" placeholder="{{ __('Rechercher un produit...') }}">
        </div>
        <div class="col-12 col-md-3 mb-2">
          <button wire:click="exportPDF" class="btn btn-danger" wire:target="exportPdf" wire:loading.attr="disabled">
              <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
              <i class="bx bx-file"></i> {{ __('Exporter PDF') }}
          </button>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>{{ __('Produit') }}</th>
                <th>{{ __('Catégorie') }}</th>
                <th>{{ __('Magasin') }}</th>
                <th>{{ __('Stock Actuel') }}</th>
                <th>{{ __('Valeur Achat') }}</th>
                <th>{{ __('Valeur Vente') }}</th>
                <th>{{ __('Bénéfice Attendu') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $prod)
                @php
                    $stock = $store_id
                        ? ($prod->stores->first()->pivot->quantity ?? 0)
                        : $prod->stores->sum('pivot.quantity');
                    $valeurAchat = $stock * $prod->purchase_price;
                    $valeurVente = $stock * $prod->sale_price;
                    $benefice = $valeurVente - $valeurAchat;
                @endphp
                <tr>
                    <td>{{ $prod->name }}</td>
                    <td>{{ $prod->category->name ?? '-' }}</td>
                    <td>
                        @if($store_id)
                            {{ $prod->stores->first()->name ?? '-' }}
                        @else
                            {{ __('Tous magasins') }}
                        @endif
                    </td>
                    <td>{{ $stock }}</td>
                    <td>{{ number_format($valeurAchat, 2, ',', ' ') }} {{ company()?->devise }}</td>
                    <td>{{ number_format($valeurVente, 2, ',', ' ') }} {{ company()?->devise }}</td>
                    <td>{{ number_format($benefice, 2, ',', ' ') }} {{ company()?->devise }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <div class="d-flex justify-content-center mt-2">
        {{ $products->links() }}
    </div>
</div>
