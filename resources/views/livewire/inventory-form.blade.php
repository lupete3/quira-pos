<div>
    <div class="row">
        <div class="col-md-8">
            <h3>{{ __('Inventaire') }}</h3>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-success" wire:click="saveInventory" wire:loading.attr="disabled">
                <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                <i class="bx bx-check me-1"></i> {{ __('Valider l\'inventaire') }}
            </button>
        </div>
    </div>

    <div>
        <select wire:model.live="selectedStoreId" class="form-select w-25 mb-3">
            <option value="">Sélectionnez un magasin</option>
            @foreach ($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
            @endforeach
        </select>

        @if ($selectedStoreId)
            {{-- Inventory Table --}}
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Produit') }}</th>
                                    <th>{{ __('Stock théorique') }}</th>
                                    <th>{{ __('Stock physique') }}</th>
                                    <th>{{ __('Différence') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr wire:key="{{ $product->id }}">
                                        <td>{{ $product->name }}</td>
                                        @php
                                            $theoretical_quantity = $product->stores->where('id', $selectedStoreId)->first()->pivot->quantity ?? 0;
                                        @endphp
                                        <td>{{ $theoretical_quantity }}</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm"
                                                wire:model.live.debounce.500ms="physical_quantities.{{ $product->id }}">
                                        </td>
                                        <td>
                                            {{ (intval($physical_quantities[$product->id] ?? 0)) - intval($theoretical_quantity) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
