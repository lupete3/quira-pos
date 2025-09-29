<div>
<div class="d-flex flex-column flex-lg-row gap-4 mb-4" style="height: 90vh;">

    <!-- Left panel: Product filters & grid -->
    <section class="flex-fill d-flex flex-column border rounded shadow-sm bg-white p-3" style="max-width: 720px;">
        <header class="mb-3">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <h4 class="m-0"><i class="bx bx-box fs-3 me-2"></i> {{ __('Produits') }}</h4>
              <select class="form-select w-auto" wire:model.lazy="selectedCategory">
                    <option value="">{{ __('Toutes catégories') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select class="form-select w-auto" wire:model.lazy="selectedBrand">
                    <option value="">{{ __('Toutes marques') }}</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filters -->
            <div class="d-flex flex-wrap gap-2 mt-2">
              <input type="search" class="form-control w-auto flex-grow-1" placeholder="{{ __('Rechercher...') }}"
                    wire:model.live="search" autofocus />
            </div>
        </header>
        <hr class="my-2" />
        <!-- Product grid -->
        <div class="flex-grow-1 overflow-none" style="min-height: 400px;">
            @if (!empty($products))
                <div class="row g-3">
                    @forelse($products as $product)
                        <div class="col-6 col-md-4" >
                            <div class="card h-100 product-card" role="button"
                                wire:click.prevent="addItem({{ $product->id }})">
                                <div class="card-body text-center p-2 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold text-truncate" title="{{ $product->name }}">
                                            {{ $product->name }}</h6>
                                        <small class="text-muted d-block">{{ __('Réf') }}: {{ $product->reference }}</small>
                                        <small class="text-muted">{{ __('Stock') }}: {{ $product->stores()->where('store_id', Auth::user()->stores()->first()->id)->first()->pivot->quantity }}</small>
                                    </div>
                                    <span
                                        class="btn btn-outline-primary fw-bold fs-6 mt-2" >{{ number_format($product->sale_price, 2) }}
                                        {{ company()?->devise }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted p-3">{{ __('Aucun produit trouvé.') }}</div>
                    @endforelse

                </div>
                <div class="mt-3 align-bottom">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>

    <!-- Middle panel: Cart items -->
    <section class="flex-fill d-flex flex-column border rounded shadow-sm bg-white p-3 mx-lg-3">
        <header class="mb-3 d-flex justify-content-between align-items-center">
            <h4 class="m-0"><i class="bx bx-shopping-bag fs-3 me-2"></i> {{ __('Panier & Facture') }}</h4>
        </header>

        <div class="flex-grow-1 overflow-auto" style="min-height: 200px;">
            @if (!$cart)
                <div class="text-center text-muted py-5">{{ __('Le panier est vide.') }}</div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach ($cart as $index => $item)
                        <div wire:key="{{ $index }}" class="card shadow-sm p-0">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">{{ $item['name'] }}</h6>
                                    <small class="text-muted">{{ __('Prix') }} : {{ number_format($item['price'], 2) }}
                                        {{ company()?->devise }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="number" min="1"
                                        class="form-control form-control-sm text-center" style="width: 75px;"
                                        value="{{ $item['quantity'] }}"
                                        wire:change="updateQuantity({{ $index }}, $event.target.value)" />
                                    <div class="fw-semibold fs-12">{{ number_format($item['subtotal'], 2) }} {{ company()?->devise }}</div>
                                    <button class="btn btn-outline-danger btn-sm"
                                        wire:click="removeItem({{ $index }})" aria-label="{{ __('Supprimer') }}">
                                        <i class="bx bx-trash fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <hr class="my-3" />

        <div class="d-flex flex-column gap-2 fs-5">
            <div class="d-flex justify-content-between">
                <span>{{ __('Sous-total') }}</span>
                <strong>{{ number_format($subtotal, 2) }} {{ company()?->devise }}</strong>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <label for="discount" class="mb-0">{{ __('Reduction') }}</label>
                <input id="discount" type="number" min="0" step="0.01"
                    class="form-control form-control-sm w-50" wire:model.live="discount" placeholder="0.00" />
            </div>

            <hr class="my-2" />

            <div class="d-flex justify-content-between fw-bold fs-4">
                <span>{{ __('Total') }}</span>
                <span>{{ number_format($total, 2) }} {{ company()?->devise }}</span>
            </div>

            <div class="d-flex justify-content-between fw-bold fs-4">
                <div class="m-2">
                    <label for="client_id" class="form-label fw-semibold">{{ __('Client') }}</label>
                    <select id="client_id" class="form-select " wire:model="client_id">
                        <option value="">{{ __('Sélectionner un client') }}</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="m-2">
                    <label for="total_paid" class="form-label fw-semibold">{{ __('Montant payé') }}</label>
                    <input id="total_paid" type="number" min="0" step="0.01"
                        class="form-control @error('total_paid') is-invalid @enderror" wire:model="total_paid"
                        placeholder="0.00" />
                    @error('total_paid')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-2" />
            <div class="d-flex justify-content-between gap-2 mt-auto">
                <button class="btn btn-success btn-lg" wire:click="saveSale" wire:loading.attr="disabled"
                    @if(!$cart) disabled @endif>
                    <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                    <i class="bx bx-check me-2"></i> {{ __('Valider la vente') }}
                </button>
                <button class="btn btn-outline-danger btn-lg" wire:click="clearCart"
                    @if(!$cart) disabled @endif>
                    <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                    <i class="bx bx-x me-2"></i> {{ __('Annuler') }}
                </button>
            </div>
        </div>

    </section>

</div>
</div>
