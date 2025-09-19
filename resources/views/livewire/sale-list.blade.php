<div>
    {{-- Recherche --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="{{ __('Rechercher par ID de vente ou nom du client...') }}" wire:model.live.debounce.300ms="search">
        </div>
        <div class="col-md-4">
            <select name="store_id" wire:model.lazy="store_id" class="form-select">
              <option value="">Tous les magasins</option>
              @forelse ($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
              @empty

              @endforelse
            </select>
        </div>
    </div>

    {{-- Tableau des ventes --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Client') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Montant total') }}</th>
                    <th>{{ __('Payé') }}</th>
                    <th>{{ __('Statut') }}</th>
                    <th>{{ __('Magasin') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($sales as $sale)
                    <tr wire:key="{{ $sale->id }}">
                        <td>{{ $sale->id }}</td>
                        <td>{{ $sale->client->name ?? __('N/A') }}</td>
                        <td>{{ $sale->sale_date }}</td>
                        <td>{{ number_format($sale->total_amount, 2) }} {{ company()->devise }}</td>
                        <td>{{ number_format($sale->total_paid, 2) }} {{ company()->devise }}</td>
                        <td>
                          @if ($sale->total_amount - $sale->total_paid <= 0)
                            <span class="badge bg-label-success me-1">{{ __('Payé') }}</span>
                          @elseif ($sale->total_paid > 0 && $sale->total_amount - $sale->total_paid > 0)
                            <span class="badge bg-label-warning me-1">{{ __('Partiellement payé') }}</span>
                          @else
                            <span class="badge bg-label-primary me-1">{{ __('Non payé') }}</span
                          @endif
                        </td>
                        <td>{{ $sale->store->name }}</td>
                        <td>
                            <button class="btn btn-info btn-sm" wire:click="viewDetails({{ $sale->id }})" data-bs-toggle="modal" data-bs-target="#saleDetailsModal">
                                <i class="bx bx-show me-1"></i> {{ __('Voir') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('Aucune vente trouvée.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $sales->links() }}
    </div>

    {{-- Modal Détails Vente --}}
    <div class="modal fade" id="saleDetailsModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Détails de la vente') }} (ID: {{ $selectedSale ? $selectedSale->id : '' }})</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Fermer') }}"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedSale)
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>{{ __('Client:') }}</strong> {{ $selectedSale->client->name ?? __('N/A') }}</p>
                                <p><strong>{{ __('Date:') }}</strong> {{ $selectedSale->sale_date }}</p>
                                <p><strong>{{ __('Statut:') }}</strong> {{ $selectedSale->status }}</p>
                                <p><strong>{{ __('Magasin:') }}</strong> {{ $selectedSale->store->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>{{ __('Montant total:') }}</strong> {{ number_format($selectedSale->total_amount, 2) }} {{ company()->devise }}</p>
                                <p><strong>{{ __('Montant payé:') }}</strong> {{ number_format($selectedSale->total_paid, 2) }} {{ company()->devise }}</p>
                                <p><strong>{{ __('Reste à payer:') }}</strong> {{ number_format($selectedSale->total_amount - $selectedSale->total_paid, 2) }} {{ company()->devise }}</p>
                                <p>
                                  @if ($selectedSale->total_amount - $selectedSale->total_paid <= 0)
                                    <span class="badge bg-label-success me-1">{{ __('Payé') }}</span>
                                  @elseif ($selectedSale->total_paid > 0 && $selectedSale->total_amount - $selectedSale->total_paid > 0)
                                    <span class="badge bg-label-warning me-1">{{ __('Partiellement payé') }}</span>
                                  @else
                                    <span class="badge bg-label-primary me-1">{{ __('Non payé') }}</span
                                  @endif
                                </p>
                            </div>
                        </div>
                        <hr>
                        <h6>{{ __('Articles:') }}</h6>
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
                                @foreach($selectedSale->items as $item)
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
                    <button type="button" class="btn btn-outline-primary" wire:click="printInvoice()" wire:loading.attr="disabled" wire:target="printInvoice">
                      <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                        <i class="bx bx-printer me-1"></i>
                      {{ __('Imprimer') }}</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
