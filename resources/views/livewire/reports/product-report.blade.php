<div>
    <select wire:model.live="selectedStoreId" class="form-select w-25 mb-3">
        <option value="">Sélectionnez un magasin</option>
        @foreach ($stores as $store)
            <option value="{{ $store->id }}">{{ $store->name }}</option>
        @endforeach
    </select>

    @if ($selectedStoreId)
        <div class="card mb-3">
            <div class="card-body row g-2">
                <div class="col-md-2">
                    <input type="text" class="form-control" placeholder="{{ __('Rechercher...') }}"
                        wire:model.live="search">
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
                    <select class="form-select" wire:model.lazy="brand_id">
                        <option value="">{{ __('Toutes marques') }}</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.lazy="stock_status">
                        <option value="">{{ __('Tous') }}</option>
                        <option value="low">{{ __('Stock faible') }}</option>
                        <option value="out">{{ __('Rupture') }}</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="number" class="form-control" placeholder="{{ __('Prix min') }}"
                        wire:model.lazy="min_price">
                </div>
                <div class="col-md-1">
                    <input type="number" class="form-control" placeholder="{{ __('Prix max') }}"
                        wire:model.lazy="max_price">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-danger" wire:click="exportPdf" wire:target="exportPdf"
                        wire:loading.attr="disabled">
                        <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                        <i class="bx bx-download"></i> {{ __('Exporter PDF') }}</button>
                </div>
            </div>
        </div>

        <div class="row text-center mb-3">
            <div class="col">
                <div class="card p-2 shadow-sm">
                    <strong>{{ __('Total Produits') }}</strong>
                    <h5>{{ $total_products }}</h5>
                </div>
            </div>
            <div class="col">
                <div class="card p-2 shadow-sm">
                    <strong>{{ __('Valeur Stock Achat') }}</strong>
                    <h5>{{ number_format($total_stock_value, 2) }} {{ company()->devise }}</h5>
                </div>
            </div>
            <div class="col">
                <div class="card p-2 shadow-sm">
                    <strong>{{ __('Valeur Stock Vente') }}</strong>
                    <h5>{{ number_format($total_stock_potential, 2) }} {{ company()->devise }}</h5>
                </div>
            </div>
            <div class="col">
                <div class="card p-2 shadow-sm">
                    <strong>{{ __('Stock Faible') }}</strong>
                    <h5 class="text-danger">{{ $low_stock }}</h5>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Ref') }}</th>
                            <th>{{ __('Nom') }}</th>
                            <th>{{ __('Catégorie') }}</th>
                            <th>{{ __('Marque') }}</th>
                            <th>{{ __('Stock') }}</th>
                            <th>{{ __('Prix Achat') }}</th>
                            <th>{{ __('Prix Vente') }}</th>
                            <th>{{ __('Valeur Stock') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $prod)
                            <tr>
                                <td>{{ $prod->reference }}</td>
                                <td>{{ $prod->name }}</td>
                                <td>{{ $prod->category?->name }}</td>
                                <td>{{ $prod->brand?->name }}</td>
                                @php
                                    $storeStock =
                                        $prod->stores->where('id', $selectedStoreId)->first()->pivot->quantity ?? 0;
                                @endphp

                                <td>
                                    <span class="@if ($storeStock <= $prod->stock_alert) text-danger @endif">
                                        {{ $storeStock }}
                                    </span>
                                </td>
                                <td>{{ number_format($prod->purchase_price, 2) }}</td>
                                <td>{{ number_format($prod->sale_price, 2) }}</td>
                                <td>{{ number_format($storeStock * $prod->sale_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">{{ __('Aucun produit trouvé') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $products->links() }}
            </div>
        </div>
    @endif
</div>
