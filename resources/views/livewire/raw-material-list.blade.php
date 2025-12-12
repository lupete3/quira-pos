<div>
    <div class="card">

        {{-- Search and Add button --}}
        <div class="card-hearder d-flex justify-content-between align-items-center m-4">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="{{ __('raw_material.search_placeholder') }}"
                    wire:model.live.debounce.300ms="search">
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="export" wire:loading.attr="disabled">
                    <i class="bx bxs-file-pdf me-1"></i> {{ __('raw_material.export') }}
                </button>
                @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                    <button class="btn btn-primary" wire:click="create" data-bs-toggle="modal"
                        data-bs-target="#rawMaterialModal">
                        <i class="bx bx-plus me-1"></i> {{ __('raw_material.add') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body">
            {{-- Table --}}
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('raw_material.designation') }}</th>
                            <th>{{ __('raw_material.purchase_price') }}</th>
                            <th>{{ __('raw_material.total_stock') }}</th>
                            <th>{{ __('raw_material.total_value') }}</th>
                            <th>{{ __('raw_material.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($rawMaterials as $material)
                            <tr wire:key="{{ $material->id }}">
                                <td><strong>{{ $material->designation }}</strong></td>
                                <td>{{ number_format($material->purchase_price, 2) }}</td>
                                <td>
                                    @php
                                        $totalStock = $material->stores->sum('pivot.quantity');
                                    @endphp
                                    <span
                                        class="badge bg-label-{{ $totalStock <= $material->min_stock ? 'danger' : 'primary' }}">
                                        {{ $totalStock }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ number_format($totalStock * $material->purchase_price, 2) }}</strong>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            {{-- Edit --}}
                                            <a class="dropdown-item" href="#" wire:click="edit({{ $material->id }})"
                                                data-bs-toggle="modal" data-bs-target="#rawMaterialModal">
                                                <i class="bx bx-edit-alt me-1"></i> {{ __('raw_material.edit') }}
                                            </a>
                                            {{-- Stock Entry (Trigger separate component or modal) --}}
                                            <a class="dropdown-item" href="#"
                                                onclick="Livewire.dispatch('openStockEntryModal', { materialId: {{ $material->id }} })">
                                                <i class="bx bx-up-arrow-alt me-1"></i> {{ __('raw_material.stock_entry') }}
                                            </a>
                                            {{-- Delete --}}
                                            <a class="dropdown-item" href="#"
                                                wire:click="confirmDelete({{ $material->id }})">
                                                <i class="bx bx-trash me-1"></i> {{ __('raw_material.delete') }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    {{ __('raw_material.not_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="card-footer mt-3">
            {{ $rawMaterials->links() }}
        </div>

    </div>

    {{-- Create/Edit Modal --}}
    <div class="modal fade" id="rawMaterialModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $isEditMode ? __('raw_material.edit_title') : __('raw_material.add_title') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('raw_material.designation') }}</label>
                            <input type="text" class="form-control @error('designation') is-invalid @enderror"
                                wire:model="designation">
                            @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('raw_material.purchase_price') }}</label>
                            <input type="number" step="0.01"
                                class="form-control @error('purchase_price') is-invalid @enderror"
                                wire:model="purchase_price">
                            @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('raw_material.min_stock') }}</label>
                            <input type="number" class="form-control @error('min_stock') is-invalid @enderror"
                                wire:model="min_stock">
                            @error('min_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">{{ __('raw_material.close') }}</button>
                        <button type="submit"
                            class="btn btn-primary">{{ $isEditMode ? __('raw_material.update') : __('raw_material.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Placeholder for Stock Entry Modal --}}
    @livewire('raw-material-stock-entry')

</div>