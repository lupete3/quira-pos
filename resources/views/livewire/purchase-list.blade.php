<div>
    {{-- Search --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="{{ __('Rechercher par ID d\'achat ou nom du fournisseur...') }}" wire:model.live.debounce.300ms="search">
        </div>
    </div>

    {{-- Purchases Table --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Fournisseur') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Montant total') }}</th>
                    <th>{{ __('Payé') }}</th>
                    <th>{{ __('Statut') }}</th>
                    <th>{{ __('Magasin') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($purchases as $purchase)
                    <tr wire:key="{{ $purchase->id }}">
                        <td>{{ $purchase->id }}</td>
                        <td>{{ $purchase->supplier->name ?? __('N/A') }}</td>
                        <td>{{ $purchase->purchase_date }}</td>
                        <td>{{ number_format($purchase->total_amount, 2) }} {{ company()->devise }}</td>
                        <td>{{ number_format($purchase->total_paid, 2) }} {{ company()->devise }}</td>
                        <td>
                          @if ($purchase->total_amount - $purchase->total_paid <= 0)
                            <span class="badge bg-label-success me-1">{{ __('Payé') }}</span>
                          @elseif ($purchase->total_paid > 0 && $purchase->total_amount - $purchase->total_paid > 0)
                            <span class="badge bg-label-warning me-1">{{ __('Partiellement payé') }}</span>
                          @else
                            <span class="badge bg-label-primary me-1">{{ __('Non payé') }}</span
                          @endif
                        </td>
                        <td>{{ $purchase->store->name ?? __('N/A') }}</td>
                        <td>
                            <button class="btn btn-info btn-sm" wire:click="viewDetails({{ $purchase->id }})" data-bs-toggle="modal" data-bs-target="#purchaseDetailsModal">
                                <i class="bx bx-show me-1"></i> {{ __('Voir') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('Aucun achat trouvé.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $purchases->links() }}
    </div>

    {{-- Purchase Details Modal --}}
    <div class="modal fade" id="purchaseDetailsModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Détails de l\'achat') }} (ID: {{ $selectedPurchase ? $selectedPurchase->id : '' }})</h5>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="{{ __('Fermer') }}"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedPurchase)
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>{{ __('Fournisseur') }}:</strong> {{ $selectedPurchase->supplier->name ?? __('N/A') }}</p>
                                <p><strong>{{ __('Date') }}:</strong> {{ $selectedPurchase->purchase_date }}</p>
                                <p><strong>{{ __('Statut') }}:</strong> {{ $selectedPurchase->status }}</p>
                                <p><strong>{{ __('Magasin') }}:</strong> {{ $selectedPurchase->store->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>{{ __('Montant total') }}:</strong> {{ number_format($selectedPurchase->total_amount, 2) }} {{ company()->devise }}</p>
                                <p><strong>{{ __('Montant payé') }}:</strong> {{ number_format($selectedPurchase->total_paid, 2) }} {{ company()->devise }}</p>
                                <p><strong>{{ __('Reste à payer') }}:</strong> {{ number_format($selectedPurchase->total_amount - $selectedPurchase->total_paid, 2) }} {{ company()->devise }}</p>
                                <p>
                                  @if ($selectedPurchase->total_amount - $selectedPurchase->total_paid <= 0)
                                    <span class="badge bg-label-success me-1">{{ __('Payé') }}</span>
                                  @elseif ($selectedPurchase->total_paid > 0 && $selectedPurchase->total_amount - $selectedPurchase->total_paid > 0)
                                    <span class="badge bg-label-warning me-1">{{ __('Partiellement payé') }}</span>
                                  @else
                                    <span class="badge bg-label-primary me-1">{{ __('Non payé') }}</span
                                  @endif
                                </p>
                            </div>
                        </div>
                        <hr>
                        <h6>{{ __('Articles') }}:</h6>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Produit') }}</th>
                                    <th>{{ __('Quantité') }}</th>
                                    <th>{{ __('Prix unitaire') }}</th>
                                    <th>{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedPurchase->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->unit_price, 2) }} {{ company()->devise }}</td>
                                    <td>{{ number_format($item->total_price, 2) }} {{ company()->devise }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
