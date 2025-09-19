<div class="row">
    {{-- Left Side: Product Search and Cart --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{ __('Produits') }}</h5>
            </div>
            <div class="card-body">
                {{-- Search Box --}}
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="{{ __('Rechercher des produits par nom ou référence...') }}" wire:model.live.debounce.300ms="search">
                </div>

                {{-- Purchase Cart Table --}}
                <div class="table-responsive text-nowrap" style="min-height: 300px;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Produit') }}</th>
                                <th style="width: 120px;">{{ __('Quantité') }}</th>
                                <th>{{ __('Prix') }}</th>
                                <th>{{ __('Sous-total') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($cart as $index => $item)
                                <tr wire:key="{{ $index }}">
                                    <td><strong>{{ $item['name'] }}</strong></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" value="{{ $item['quantity'] }}" wire:change="updateQuantity({{ $index }}, $event.target.value)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control form-control-sm" value="{{ $item['price'] }}" wire:change="updatePrice({{ $index }}, $event.target.value)">
                                    </td>
                                    <td>{{ number_format($item['subtotal'], 2) }}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" wire:click="removeItem({{ $index }})">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('Le panier est vide.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Product List for Search --}}
        @if(!empty($products))
        <div class="card mt-3">
            <div class="card-body">
                <div class="list-group">
                    @forelse($products as $product)
                        <a href="#" class="list-group-item list-group-item-action" wire:click.prevent="addItem({{ $product->id }})">
                            <strong>{{ $product->name }}</strong> ({{ __('Réf') }}: {{ $product->reference }}) - {{ __('Stock') }}: {{ $product->stock_quantity }}
                        </a>
                    @empty
                        <div class="list-group-item">{{ __('Aucun produit trouvé.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Right Side: Supplier, Total and Actions --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{ __('Détails de l\'achat') }}</h5>
            </div>
            <div class="card-body">
              {{-- Store Selection --}}
              <div class="mb-3">
                  <label for="store_id" class="form-label">{{ __('Magasin') }}</label>
                  <select class="form-select @error('store_id') is-invalid @enderror" wire:model="store_id">
                      <option value="">{{ __('Sélectionner un magasin') }}</option>
                      @foreach($stores as $store)
                          <option value="{{ $store->id }}">{{ $store->name }}</option>
                      @endforeach
                  </select>
                  @error('store_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

                {{-- Supplier Selection --}}
                <div class="mb-3">
                    <label for="supplier_id" class="form-label">{{ __('Fournisseur') }}</label>
                    <select class="form-select @error('supplier_id') is-invalid @enderror" wire:model="supplier_id">
                        <option value="">{{ __('Sélectionner un fournisseur') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr>

                {{-- Totals --}}
                <h4 class="d-flex justify-content-between mt-3">
                    <span>{{ __('Total') }}</span>
                    <strong>{{ number_format($total, 2) }} {{ company()->devise }}</strong>
                </h4>

                <hr>

                {{-- Payment --}}
                <div class="mb-3">
                    <label for="total_paid" class="form-label">{{ __('Montant payé') }}</label>
                    <input type="number" class="form-control @error('total_paid') is-invalid @enderror" wire:model="total_paid" placeholder="0.00">
                    @error('total_paid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Actions --}}
                <div class="d-grid gap-2">
                    <button class="btn btn-success" wire:click="savePurchase" wire:loading.attr="disabled">
                        <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                        <i class="bx bx-check me-1"></i> {{ __('Finaliser l\'achat') }}
                    </button>
                    <button class="btn btn-danger" wire:click="clearCart" wire:loading.attr="disabled">
                        <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                        <i class="bx bx-x me-1"></i> {{ __('Annuler') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
