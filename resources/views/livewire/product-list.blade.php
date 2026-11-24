<div>
    {{-- Search and Add button --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            {{-- Clé : rechercher_produits --}}
            <input type="text" class="form-control" placeholder="{{ __('product.rechercher_produits') }}" wire:model.live.debounce.300ms="search">
        </div>
        <button class="btn btn-primary" wire:click="create" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="bx bx-plus me-1"></i> {{-- Clé : ajouter --}}
            {{ __('product.ajouter') }}
        </button>
    </div>

    {{-- Products Table --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    {{-- Clés pour l'en-tête du tableau --}}
                    <th style="width: 64px;">{{ __('product.photo') }}</th>
                    <th>{{ __('product.ref') }}</th>
                    <th>{{ __('product.nom') }}</th>
                    <th>{{ __('product.categorie') }}</th>
                    <th>{{ __('product.prix_vente') }} ({{ company()?->devise }})</th>
                    <th>{{ __('product.stock') }}</th>
                    <th>{{ __('product.actions') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($products as $product)
                    <tr wire:key="{{ $product->id }}">
                        <td>
                            <a href="#" data-photo-viewer class="d-inline-block"
                               data-photo-src="{{ $product->photo_url }}"
                               data-photo-title="{{ $product->name }}"
                               data-photo-subtitle="{{ __('product.ref') }} {{ $product->reference }}"
                               data-photo-detail-url="{{ route('products.show', $product) }}"
                               title="{{ __('product.agrandir_photo') }}">
                                <img src="{{ $product->photo_url }}" alt="{{ $product->name }}"
                                     class="rounded border" loading="lazy"
                                     style="width: 44px; height: 44px; object-fit: cover; background: #f1f3f6; cursor: zoom-in;"
                                     onerror="this.onerror=null;this.src='{{ asset(\App\Models\Product::PHOTO_PLACEHOLDER) }}';">
                            </a>
                        </td>
                        <td>{{ $product->reference }}</td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="text-body" title="{{ __('product.voir_details') }}">
                                <strong>{{ $product->name }}</strong>
                            </a>
                        </td>
                        {{-- Clé : na --}}
                        <td>{{ $product->category->name ?? __('product.na') }}</td>
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
                                    <a class="dropdown-item" href="{{ route('products.show', $product) }}">
                                        <i class="bx bx-show me-1"></i>
                                        {{ __('product.voir_details') }}
                                    </a>
                                    <a class="dropdown-item" href="#" wire:click="edit({{ $product->id }})" data-bs-toggle="modal" data-bs-target="#productModal">
                                        <i class="bx bx-edit-alt me-1"></i> {{-- Clé : modifier --}}
                                        {{ __('product.modifier') }}
                                    </a>
                                    <a class="dropdown-item" href="#" wire:click="confirmDelete({{ $product->id }})">
                                        <i class="bx bx-trash me-1"></i> {{-- Clé : supprimer --}}
                                        {{ __('product.supprimer') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            {{-- Clé : aucun_produit --}}
                            {{ __('product.aucun_produit') }}
                        </td>
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
                    {{-- Clés conditionnelles pour le titre du modal --}}
                    <h5 class="modal-title">{{ $isEditMode ? __('product.modifier_produit') : __('product.creer_produit') }}</h5>
                    {{-- Clé : fermer --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('product.fermer') }}"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                      {{-- Stock par magasin dans le magasin --}}
                      <div class="row">
                          @foreach($stores as $store)
                              @if (Auth::user()->role_id != 3)
                              <div class="col-md-4 mb-3">
                                  {{-- Utilisation de $store->name (ne nécessite pas de traduction) --}}
                                  <label class="form-label">{{ $store->name }} ({{ __('product.stock') }})</label>
                                  <input type="number" class="form-control"
                                          wire:model="storeQuantities.{{ $store->id }}"
                                          placeholder="0" value="0">
                              </div>
                              @endif
                          @endforeach
                      </div>
                      {{-- Photo du produit --}}
                      <div class="d-flex align-items-center gap-3 mb-3">
                          @php
                              $previewUrl = null;
                              if ($photo) {
                                  try { $previewUrl = $photo->temporaryUrl(); } catch (\Throwable $e) { $previewUrl = null; }
                              }
                              $previewUrl = $previewUrl ?: $existingPhotoUrl;
                          @endphp
                          <div class="position-relative flex-shrink-0">
                              <img src="{{ $previewUrl ?: asset(\App\Models\Product::PHOTO_PLACEHOLDER) }}"
                                   alt="{{ __('product.photo') }}" class="rounded border"
                                   style="width: 96px; height: 96px; object-fit: cover; background: #f1f3f6;">
                              <div wire:loading.flex wire:target="photo"
                                   class="position-absolute top-0 start-0 w-100 h-100 align-items-center justify-content-center rounded"
                                   style="background: rgba(255,255,255,.7);">
                                  <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                              </div>
                          </div>
                          <div class="flex-grow-1">
                              <label class="form-label mb-1">{{ __('product.photo') }} <small class="text-muted">({{ __('product.optionnel') }})</small></label>
                              <div class="d-flex flex-wrap gap-2">
                                  <label class="btn btn-sm btn-outline-primary mb-0">
                                      <i class="bx bx-upload me-1"></i>
                                      {{ $previewUrl ? __('product.changer_photo') : __('product.ajouter_photo') }}
                                      <input type="file" class="d-none" accept="image/png,image/jpeg,image/webp"
                                             wire:model="photo" wire:key="photo-input-{{ $productId ?? 'new' }}">
                                  </label>
                                  @if ($previewUrl)
                                      <button type="button" class="btn btn-sm btn-outline-danger" wire:click="clearPhoto">
                                          <i class="bx bx-trash me-1"></i> {{ __('product.retirer_photo') }}
                                      </button>
                                  @endif
                              </div>
                              <small class="text-muted d-block mt-1">{{ __('product.format_photo') }}</small>
                              @error('photo') <div class="text-danger small">{{ $message }}</div> @enderror
                          </div>
                      </div>

                      {{-- Nom et Référence --}}
                      <div class="row">
                          <div class="col-md-6 mb-3">
                              {{-- Clé : nom_produit --}}
                              <label for="name" class="form-label">{{ __('product.nom_produit') }}</label>
                              {{-- Clé : entrez_nom_produit --}}
                              <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="{{ __('product.entrez_nom_produit') }}">
                              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                          </div>
                          <div class="col-md-6 mb-3">
                              {{-- Clé : reference --}}
                              <label for="reference" class="form-label">{{ __('product.reference') }}</label>
                              {{-- Clé : entrez_reference --}}
                              <input type="text" class="form-control @error('reference') is-invalid @enderror" wire:model="reference" placeholder="{{ __('product.entrez_reference') }}">
                              @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                          </div>
                      </div>

                      {{-- Catégorie, Marque et Unité --}}
                      <div class="row">
                          {{-- === AUTOCOMPLETE CATEGORIE === --}}
                          <div class="col-md-4 mb-3" x-data="{ open: false }">
                              <label class="form-label">{{ __('product.categorie_label') }}</label>

                              <div class="input-group">
                                  <span class="input-group-text"><i class="bx bx-category"></i></span>

                                  <input type="text"
                                      class="form-control"
                                      placeholder="{{ __('product.selectionner_categorie') }}"
                                      wire:model.live.debounce.300ms="categorySearch"
                                      @focus="open = true"
                                      @click.outside="open = false">
                              </div>

                              {{-- Résultats --}}
                              <div class="card shadow-sm mt-1" x-show="open" style="max-height: 180px; overflow: auto;">
                                  @forelse ($categoryResults as $cat)
                                      <a href="javascript:void(0)"
                                        wire:click="chooseCategory({{ $cat['id'] }}, '{{ $cat['name'] }}')"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                          <i class="bx bx-folder me-2 text-primary"></i>
                                          {{ $cat['name'] }}
                                      </a>
                                  @empty

                                  @endforelse
                              </div>

                              @error('category_name') <div class="text-danger small">{{ $message }}</div> @enderror
                          </div>

                          {{-- === AUTOCOMPLETE MARQUE === --}}
                          <div class="col-md-4 mb-3" x-data="{ open: false }">
                              <label class="form-label">{{ __('product.marque') }}</label>

                              <div class="input-group">
                                  <span class="input-group-text"><i class="bx bx-category"></i></span>

                                  <input type="text"
                                      class="form-control"
                                      placeholder="{{ __('product.selectionner_marque') }}"
                                      wire:model.live.debounce.300ms="brandSearch"
                                      @focus="open = true"
                                      @click.outside="open = false">
                              </div>

                              {{-- Résultats --}}
                              <div class="card shadow-sm mt-1" x-show="open" style="max-height: 180px; overflow: auto;">
                                  @forelse ($brandResults as $bra)
                                      <a href="javascript:void(0)"
                                        wire:click="chooseBrand({{ $bra['id'] }}, '{{ $bra['name'] }}')"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                          <i class="bx bx-folder me-2 text-primary"></i>
                                          {{ $bra['name'] }}
                                      </a>
                                  @empty

                                  @endforelse
                              </div>

                              @error('brand_name') <div class="text-danger small">{{ $message }}</div> @enderror
                          </div>

                          {{-- === AUTOCOMPLETE UNITE === --}}
                          <div class="col-md-4 mb-3" x-data="{ open: false }">
                              <label class="form-label">{{ __('product.unite') }}</label>

                              <div class="input-group">
                                  <span class="input-group-text"><i class="bx bx-category"></i></span>

                                  <input type="text"
                                      class="form-control"
                                      placeholder="{{ __('product.selectionner_unite') }}"
                                      wire:model.live.debounce.300ms="unitSearch"
                                      @focus="open = true"
                                      @click.outside="open = false">
                              </div>

                              {{-- Résultats --}}
                              <div class="card shadow-sm mt-1" x-show="open" style="max-height: 180px; overflow: auto;">
                                  @forelse ($unitResults as $unit)
                                      <a href="javascript:void(0)"
                                        wire:click="chooseUnit({{ $unit['id'] }}, '{{ $unit['name'] }}')"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                          <i class="bx bx-folder me-2 text-primary"></i>
                                          {{ $unit['name'] }}
                                      </a>
                                  @empty

                                  @endforelse
                              </div>

                              @error('unit_name') <div class="text-danger small">{{ $message }}</div> @enderror
                          </div>
                      </div>

                      {{-- Prix d'achat et de vente --}}
                      <div class="row">
                          @if (Auth::user()->role_id != 3)
                          <div class="col-md-4 mb-3">
                              {{-- Clé : prix_achat --}}
                              <label for="purchase_price" class="form-label">{{ __('product.prix_achat') }}</label>
                              <input type="number" step="0.01" class="form-control @error('purchase_price') is-invalid @enderror" wire:model="purchase_price" placeholder="0.00">
                              @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                          </div>
                          @endif
                          <div class="col-md-4 mb-3">
                              {{-- Clé : prix_vente_label --}}
                              <label for="sale_price" class="form-label">{{ __('product.prix_vente_label') }}</label>
                              <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror" wire:model="sale_price" placeholder="0.00">
                              @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                          </div>
                      
                          <div class="col-md-4 mb-3">
                              {{-- Clé : stock_minimum --}}
                              <label for="min_stock" class="form-label">{{ __('product.stock_minimum') }}</label>
                              <input type="number" class="form-control @error('min_stock') is-invalid @enderror" wire:model="min_stock" placeholder="0">
                              @error('min_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                          </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                        {{-- Clé : fermer --}}
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('product.fermer') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                            <i class="bx bx-check me-1"></i>
                            {{-- Clés conditionnelles pour le bouton d'action --}}
                          {{ $isEditMode ? __('product.enregistrer_modifications') : __('product.creer') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
