<div>
    {{-- Search and Add button --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="{{ __('Rechercher des produits par nom ou référence...') }}" wire:model.live.debounce.300ms="search">
        </div>
        <button class="btn btn-primary" wire:click="create" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="bx bx-plus me-1"></i> {{ __('Ajouter') }}
        </button>
    </div>

    {{-- Products Table --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Réf.') }}</th>
                    <th>{{ __('Nom') }}</th>
                    <th>{{ __('Catégorie') }}</th>
                    <th>{{ __('Prix de vente') }} ({{ company()?->devise }})</th>
                    <th>{{ __('Stock') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($products as $product)
                    <tr wire:key="{{ $product->id }}">
                        <td>{{ $product->reference }}</td>
                        <td><strong>{{ $product->name }}</strong></td>
                        <td>{{ $product->category->name ?? __('N/A') }}</td>
                        <td>{{ $product->sale_price }} {{ company()?->devise }}</td>
                        <td>
                            @foreach($product->stores as $store)
                                <span class="badge bg-label-{{ $store->pivot->quantity <= $product->min_stock ? 'danger' : 'primary' }}">
                                    {{ $store->name }} : {{ $store->pivot->quantity }} {{ $product->unit->abbreviation ?? '' }}
                                </span><br>
                            @endforeach
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" wire:click="edit({{ $product->id }})" data-bs-toggle="modal" data-bs-target="#productModal">
                                        <i class="bx bx-edit-alt me-1"></i> {{ __('Modifier') }}
                                    </a>
                                    <a class="dropdown-item" href="#" wire:click="confirmDelete({{ $product->id }})">
                                        <i class="bx bx-trash me-1"></i> {{ __('Supprimer') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ __('Aucun produit trouvé.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $products->links() }}
    </div>

    {{-- Product Modal --}}
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? __('Modifier le produit') : __('Créer un produit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Fermer') }}"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                      <div class="row">
                          @foreach($stores as $store)
                              <div class="col-md-4 mb-3">
                                  <label class="form-label">{{ $store->name }} (Stock)</label>
                                  <input type="number" class="form-control"
                                        wire:model="storeQuantities.{{ $store->id }}"
                                        placeholder="0" value="0">
                              </div>
                          @endforeach
                      </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">{{ __('Nom du produit') }}</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="{{ __('Entrez le nom du produit') }}">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reference" class="form-label">{{ __('Référence') }}</label>
                                <input type="text" class="form-control @error('reference') is-invalid @enderror" wire:model="reference" placeholder="{{ __('Entrez la référence') }}">
                                @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="category_id" class="form-label">{{ __('Catégorie') }}</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" wire:model="category_id">
                                    <option value="">{{ __('Sélectionner une catégorie') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="brand_id" class="form-label">{{ __('Marque') }}</label>
                                <select class="form-select @error('brand_id') is-invalid @enderror" wire:model="brand_id">
                                    <option value="">{{ __('Sélectionner une marque') }}</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="unit_id" class="form-label">{{ __('Unité') }}</label>
                                <select class="form-select @error('unit_id') is-invalid @enderror" wire:model="unit_id">
                                    <option value="">{{ __('Sélectionner une unité') }}</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="purchase_price" class="form-label">{{ __('Prix d\'achat') }}</label>
                                <input type="number" step="0.01" class="form-control @error('purchase_price') is-invalid @enderror" wire:model="purchase_price" placeholder="0.00">
                                @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sale_price" class="form-label">{{ __('Prix de vente') }}</label>
                                <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror" wire:model="sale_price" placeholder="0.00">
                                @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="min_stock" class="form-label">{{ __('Stock minimum') }}</label>
                                <input type="number" class="form-control @error('min_stock') is-invalid @enderror" wire:model="min_stock" placeholder="0">
                                @error('min_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                            <i class="bx bx-check me-1"></i>
                          {{ $isEditMode ? __('Enregistrer les modifications') : __('Créer') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
