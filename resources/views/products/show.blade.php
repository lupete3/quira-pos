@php
    $devise = company()?->devise;
    $unitAbbr = $product->unit->abbreviation ?? '';
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $fmtQty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',');
    $fmtDate = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d/m/Y') : '—';
@endphp

<x-layouts.app>
    <x-slot:title>
        {{ $product->name }} – {{ __('product.detail_produit') }}
    </x-slot:title>

    {{-- En-tête --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <a href="{{ route('products.index') }}" class="text-muted small">
                <i class="bx bx-arrow-back me-1"></i>{{ __('product.retour_liste') }}
            </a>
            <h4 class="mb-0 mt-1">{{ $product->name }}</h4>
        </div>
        <span class="badge bg-label-secondary fs-6">{{ __('product.ref') }} {{ $product->reference }}</span>
    </div>

    <div class="row g-4">
        {{-- Colonne gauche : photo + infos --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <a href="#" data-photo-viewer
                       data-photo-src="{{ $product->photo_url }}"
                       data-photo-title="{{ $product->name }}"
                       data-photo-subtitle="{{ __('product.ref') }} {{ $product->reference }}"
                       class="d-block position-relative rounded overflow-hidden border mb-3"
                       style="background: #f1f3f6;" title="{{ __('product.agrandir_photo') }}">
                        <img src="{{ $product->photo_url }}" alt="{{ $product->name }}"
                             class="w-100" style="aspect-ratio: 1 / 1; object-fit: cover;"
                             onerror="this.onerror=null;this.src='{{ asset(\App\Models\Product::PHOTO_PLACEHOLDER) }}';">
                        <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-75">
                            <i class="bx bx-zoom-in"></i>
                        </span>
                    </a>
                    @unless ($product->hasPhoto())
                        <p class="text-muted small text-center mb-3">{{ __('product.aucune_photo') }}</p>
                    @endunless

                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">{{ __('product.categorie') }}</dt>
                        <dd class="col-7">{{ $product->category->name ?? __('product.na') }}</dd>

                        <dt class="col-5 text-muted fw-normal">{{ __('product.marque') }}</dt>
                        <dd class="col-7">{{ $product->brand->name ?? __('product.na') }}</dd>

                        <dt class="col-5 text-muted fw-normal">{{ __('product.unite') }}</dt>
                        <dd class="col-7">{{ $product->unit->name ?? __('product.na') }}{{ $unitAbbr ? ' (' . $unitAbbr . ')' : '' }}</dd>

                        <dt class="col-5 text-muted fw-normal">{{ __('product.stock_minimum') }}</dt>
                        <dd class="col-7">{{ $product->min_stock }} {{ $unitAbbr }}</dd>

                        <dt class="col-5 text-muted fw-normal">{{ __('product.cree_le') }}</dt>
                        <dd class="col-7">{{ $fmtDate($product->created_at) }}</dd>

                        <dt class="col-5 text-muted fw-normal">{{ __('product.modifie_le') }}</dt>
                        <dd class="col-7 mb-0">{{ $fmtDate($product->updated_at) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Colonne droite --}}
        <div class="col-lg-8">
            {{-- Indicateurs --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card h-100"><div class="card-body p-3">
                        <small class="text-muted d-block">{{ __('product.prix_vente_label') }}</small>
                        <span class="fs-5 fw-semibold">{{ $fmt($product->sale_price) }}</span> <small>{{ $devise }}</small>
                    </div></div>
                </div>
                @if ($canSeePurchase)
                <div class="col-6 col-md-3">
                    <div class="card h-100"><div class="card-body p-3">
                        <small class="text-muted d-block">{{ __('product.prix_achat') }}</small>
                        <span class="fs-5 fw-semibold">{{ $fmt($product->purchase_price) }}</span> <small>{{ $devise }}</small>
                        <small class="d-block {{ $stats['margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ __('product.marge') }} : {{ $fmt($stats['margin']) }}
                        </small>
                    </div></div>
                </div>
                @endif
                <div class="col-6 col-md-3">
                    <div class="card h-100"><div class="card-body p-3">
                        <small class="text-muted d-block">{{ __('product.stock_total') }}</small>
                        <span class="fs-5 fw-semibold {{ $stats['total_stock'] <= $product->min_stock ? 'text-danger' : '' }}">{{ $stats['total_stock'] }}</span>
                        <small>{{ $unitAbbr }}</small>
                    </div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100"><div class="card-body p-3">
                        <small class="text-muted d-block">{{ __('product.quantite_vendue') }}</small>
                        <span class="fs-5 fw-semibold">{{ $fmtQty($stats['sold_quantity']) }}</span> <small>{{ $unitAbbr }}</small>
                        <small class="d-block text-muted">{{ __('product.derniere_vente') }} : {{ $fmtDate($stats['last_sale_date']) }}</small>
                    </div></div>
                </div>
            </div>

            {{-- Stock par magasin --}}
            <div class="card mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="mb-0">{{ __('product.stock_par_magasin') }}</h6>
                    <small class="text-muted">
                        {{ __('product.valeur_stock_vente') }} : <strong>{{ $fmt($stats['stock_sale_value']) }} {{ $devise }}</strong>
                        @if ($canSeePurchase)
                            · {{ __('product.valeur_stock_achat') }} : <strong>{{ $fmt($stats['stock_cost_value']) }} {{ $devise }}</strong>
                        @endif
                    </small>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr>
                            <th>{{ __('product.magasin') }}</th>
                            <th class="text-end">{{ __('product.quantite') }}</th>
                            <th class="text-end">{{ __('product.statut') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($product->stores as $store)
                                @php $q = (int) $store->pivot->quantity; @endphp
                                <tr>
                                    <td>{{ $store->name }}</td>
                                    <td class="text-end">{{ $q }} {{ $unitAbbr }}</td>
                                    <td class="text-end">
                                        @if ($q <= 0)
                                            <span class="badge bg-label-danger">{{ __('product.rupture') }}</span>
                                        @elseif ($q <= $product->min_stock)
                                            <span class="badge bg-label-warning">{{ __('product.stock_faible') }}</span>
                                        @else
                                            <span class="badge bg-label-success">{{ __('product.disponible') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">{{ __('product.aucun_stock') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Dernières ventes --}}
            <div class="card mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="mb-0">{{ __('product.dernieres_ventes') }}</h6>
                    <small class="text-muted">{{ __('product.chiffre_affaires') }} : <strong>{{ $fmt($stats['sales_revenue']) }} {{ $devise }}</strong></small>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr>
                            <th>{{ __('product.date') }}</th>
                            <th>{{ __('product.client') }}</th>
                            <th>{{ __('product.magasin') }}</th>
                            <th class="text-end">{{ __('product.quantite') }}</th>
                            <th class="text-end">{{ __('product.prix_unitaire') }}</th>
                            <th class="text-end">{{ __('product.total') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($recentSales as $item)
                                <tr>
                                    <td>{{ $fmtDate($item->sale?->sale_date ?? $item->created_at) }}</td>
                                    <td>{{ $item->sale?->client?->name ?? __('product.client_comptoir') }}</td>
                                    <td>{{ $item->sale?->store?->name ?? '—' }}</td>
                                    <td class="text-end">{{ $fmtQty($item->quantity) }}</td>
                                    <td class="text-end">{{ $fmt($item->unit_price) }}</td>
                                    <td class="text-end">{{ $fmt($item->total_price) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">{{ __('product.aucune_vente') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Derniers achats (masqué pour le caissier) --}}
            @if ($canSeePurchase)
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ __('product.derniers_achats') }}</h6></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr>
                            <th>{{ __('product.date') }}</th>
                            <th>{{ __('product.fournisseur') }}</th>
                            <th>{{ __('product.magasin') }}</th>
                            <th class="text-end">{{ __('product.quantite') }}</th>
                            <th class="text-end">{{ __('product.prix_unitaire') }}</th>
                            <th class="text-end">{{ __('product.total') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($recentPurchases as $item)
                                <tr>
                                    <td>{{ $fmtDate($item->purchase?->purchase_date ?? $item->created_at) }}</td>
                                    <td>{{ $item->purchase?->supplier?->name ?? '—' }}</td>
                                    <td>{{ $item->purchase?->store?->name ?? '—' }}</td>
                                    <td class="text-end">{{ $fmtQty($item->quantity) }}</td>
                                    <td class="text-end">{{ $fmt($item->unit_price) }}</td>
                                    <td class="text-end">{{ $fmt($item->total_price) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">{{ __('product.aucun_achat') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    @include('products.partials.photo-viewer')
</x-layouts.app>
