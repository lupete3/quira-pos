<div>
    {{-- Recherche et bouton Ajouter --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text"
                   class="form-control"
                   placeholder="{{ __('Rechercher une marque...') }}"
                   wire:model.live.debounce.300ms="search">
        </div>
        <button class="btn btn-primary"
                wire:click="create"
                data-bs-toggle="modal"
                data-bs-target="#brandModal">
            <i class="bx bx-plus me-1"></i> {{ __('Ajouter') }}
        </button>
    </div>

    {{-- Tableau des marques --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nom') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($brands as $index => $brand)
                    <tr wire:key="{{ $brand->id }}">
                        <td>{{ $index+1 }}</td>
                        <td><strong>{{ $brand->name }}</strong></td>
                        <td>
                            <div class="dropdown">
                                <button type="button"
                                        class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item"
                                       href="#"
                                       wire:click="edit({{ $brand->id }})"
                                       data-bs-toggle="modal"
                                       data-bs-target="#brandModal">
                                        <i class="bx bx-edit-alt me-1"></i> {{ __('Modifier') }}
                                    </a>
                                    <a class="dropdown-item"
                                       href="#"
                                       wire:click="confirmDelete({{ $brand->id }})">
                                        <i class="bx bx-trash me-1"></i> {{ __('Supprimer') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">{{ __('Aucune marque trouvée.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $brands->links() }}
    </div>

    {{-- Modal de marque --}}
    <div class="modal fade" id="brandModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $isEditMode ? __('Modifier la marque') : __('Créer une marque') }}
                    </h5>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="{{ __('Fermer') }}"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nom') }}</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   wire:model="name"
                                   placeholder="{{ __('Entrer le nom de la marque') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">{{ __('Fermer') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                            <i class="bx bx-check me-1"></i>
                            {{-- <i class="bx bx-check me-1"></i> --- IGNORE --- --}}
                            {{ $isEditMode ? __('Enregistrer les modifications') : __('Créer') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
