<div>
    {{-- Bouton Ajouter --}}
    <div class="d-flex justify-content-end align-items-center mb-3">
        <button class="btn btn-primary" wire:click="create" data-bs-toggle="modal" data-bs-target="#purchaseReturnModal">
            <i class="bx bx-plus me-1"></i> {{ __('Ajouter') }}
        </button>
    </div>

    {{-- Tableau des retours d'achat --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('ID Achat') }}</th>
                    <th>{{ __('Produit') }}</th>
                    <th>{{ __('Quantité') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Raison') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($purchaseReturns as $return)
                    <tr wire:key="{{ $return->id }}">
                        <td>{{ $return->id }}</td>
                        <td><a href="#">{{ $return->purchase_id }}</a></td>
                        <td>{{ $return->product->name ?? __('N/A') }}</td>
                        <td>{{ $return->quantity }}</td>
                        <td>{{ $return->return_date }}</td>
                        <td>{{ Str::limit($return->reason, 30) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ __('Aucun retour d\'achat trouvé.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $purchaseReturns->links() }}
    </div>

    {{-- Modal Retour d'achat --}}
    <div class="modal fade" id="purchaseReturnModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Créer un retour d\'achat') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Fermer') }}"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('ID Achat') }}</label>
                            <input type="number" class="form-control @error('purchase_id') is-invalid @enderror" wire:model.live="purchase_id" placeholder="{{ __('Entrez l\'ID de l\'achat') }}">
                            @error('purchase_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($purchaseProducts)
                        <div class="mb-3">
                            <label class="form-label">{{ __('Produit') }}</label>
                            <select class="form-select @error('product_id') is-invalid @enderror" wire:model="product_id">
                                <option value="">{{ __('Sélectionnez un produit') }}</option>
                                @foreach($purchaseProducts as $item)
                                    <option value="{{ $item->product->id }}">{{ $item->product->name }} ({{ __('Acheté') }}: {{ $item->quantity }})</option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">{{ __('Quantité') }}</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" wire:model="quantity" placeholder="{{ __('Entrez la quantité à retourner') }}">
                            @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Raison') }}</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" wire:model="reason" rows="3"></textarea>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                          <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                          {{ __('Enregistrer le retour') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
