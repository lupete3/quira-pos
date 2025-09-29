<div>
    {{-- Recherche --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="{{ __('Rechercher par nom du fournisseur...') }}" wire:model.live.debounce.300ms="search">
        </div>
    </div>

    {{-- Tableau des dettes des fournisseurs --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nom du fournisseur') }}</th>
                    <th>{{ __('Dette totale') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($suppliers as $supplier)
                    <tr wire:key="{{ $supplier->id }}">
                        <td>{{ $supplier->id }}</td>
                        <td><strong>{{ $supplier->name }}</strong></td>
                        <td>{{ number_format($supplier->debt, 2) }} {{ company()?->devise }}</td>
                        <td>
                            <button class="btn btn-success btn-sm" wire:click="selectSupplier({{ $supplier->id }})" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="bx bx-dollar me-1"></i> {{ __('Ajouter un paiement') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">{{ __('Aucun fournisseur avec dette trouvé.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $suppliers->links() }}
    </div>

    {{-- Modal de paiement --}}
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Ajouter un paiement pour') }} {{ $selectedSupplier ? $selectedSupplier->name : '' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Fermer') }}"></button>
                </div>
                <form wire:submit.prevent="savePayment">
                    <div class="modal-body">
                        @if($selectedSupplier)
                            <p><strong>{{ __('Dette actuelle :') }}</strong> {{ number_format($selectedSupplier->debt, 2) }} {{ company()?->devise }}</p>

                            {{-- Sélection facture --}}
                            <div class="mb-3">
                                <label class="form-label">{{ __('Sélectionner une facture impayée') }}</label>
                                <select class="form-select @error('selectedPurchase') is-invalid @enderror" wire:model="selectedPurchase">
                                    <option value="">{{ __('-- Choisir une facture --') }}</option>
                                    @foreach($purchasesUnpaid as $purchase)
                                        @php
                                            $reste = $purchase->total_amount - $purchase->total_paid;
                                        @endphp
                                        <option value="{{ $purchase->id }}">
                                            Facture #{{ $purchase->id }} - Total: {{ number_format($purchase->total_amount,2) }} /
                                            Payé: {{ number_format($purchase->total_paid,2) }} /
                                            Reste: {{ number_format($reste,2) }} {{ company()?->devise }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedPurchase') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif
                        <div class="mb-3">
                            <label for="payment_amount" class="form-label">{{ __('Montant du paiement') }}</label>
                            <input type="number" step="0.01" class="form-control @error('payment_amount') is-invalid @enderror" wire:model="payment_amount" placeholder="0.00">
                            @error('payment_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="payment_description" class="form-label">{{ __('Description (optionnel)') }}</label>
                            <textarea class="form-control" wire:model="payment_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                          <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                          {{ __('Enregistrer le paiement') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
