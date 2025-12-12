<div>
    <div class="modal fade" id="stockEntryModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('raw_material.stock_movement') }}: {{ $rawMaterial?->designation }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">

                        {{-- Type --}}
                        <div class="mb-3">
                            <label class="form-label">{{ __('raw_material.movement_type') }}</label>
                            <select class="form-select @error('type') is-invalid @enderror" wire:model="type">
                                <option value="entry">{{ __('raw_material.entry_stock') }}</option>
                                <option value="exit">{{ __('raw_material.exit_stock') }}</option>
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Store --}}
                        <div class="mb-3">
                            <label class="form-label">{{ __('raw_material.store') }}</label>
                            <select class="form-select @error('store_id') is-invalid @enderror" wire:model="store_id">
                                <option value="">{{ __('raw_material.choose_store') }}</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                            @error('store_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Quantity --}}
                        <div class="mb-3">
                            <label class="form-label">{{ __('raw_material.quantity') }}</label>
                            <input type="number" step="0.01"
                                class="form-control @error('quantity') is-invalid @enderror" wire:model="quantity">
                            @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label">{{ __('raw_material.description_reason') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                wire:model="description" rows="2"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">{{ __('raw_material.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('raw_material.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('open-stock-modal', (event) => {
                var modal = new bootstrap.Modal(document.getElementById('stockEntryModal'));
                modal.show();
            });
            @this.on('close-stock-modal', (event) => {
                var el = document.getElementById('stockEntryModal');
                var modal = bootstrap.Modal.getInstance(el);
                if (modal) modal.hide();
            });
        });
    </script>
</div>