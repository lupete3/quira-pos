<div>
    {{-- Barre de recherche et bouton d'ajout --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="{{ __('Rechercher fournisseurs par nom, email ou téléphone...') }}" wire:model.live.debounce.300ms="search">
        </div>
        <button class="btn btn-primary" wire:click="create" data-bs-toggle="modal" data-bs-target="#supplierModal">
            <i class="bx bx-plus me-1"></i> {{ __('Ajouter') }}
        </button>
    </div>

    {{-- Tableau des fournisseurs --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nom') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Téléphone') }}</th>
                    <th>{{ __('Dette') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($suppliers as $index => $supplier)
                    <tr wire:key="{{ $supplier->id }}">
                        <td>{{ $index+1 }}</td>
                        <td><strong>{{ $supplier->name }}</strong></td>
                        <td>{{ $supplier->email }}</td>
                        <td>{{ $supplier->phone }}</td>
                        <td>{{ number_format($supplier->debt, 2) }} {{ company()?->devise }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" wire:click="edit({{ $supplier->id }})" data-bs-toggle="modal" data-bs-target="#supplierModal">
                                        <i class="bx bx-edit-alt me-1"></i> {{ __('Éditer') }}
                                    </a>
                                    @if (Auth::user()->role_id == 1)
                                    <a class="dropdown-item" href="#" wire:click="confirmDelete({{ $supplier->id }})">
                                        <i class="bx bx-trash me-1"></i> {{ __('Supprimer') }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ __('Aucun fournisseur trouvé.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $suppliers->links() }}
    </div>

    {{-- Modal Création/Édition Fournisseur --}}
    <div class="modal fade" id="supplierModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? __('Éditer le fournisseur') : __('Créer un fournisseur') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Fermer') }}"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Nom') }}</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="{{ __('Nom du fournisseur') }}">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" placeholder="{{ __('Email') }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Téléphone') }}</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" wire:model="phone" placeholder="{{ __('Numéro de téléphone') }}">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Dette initiale') }}</label>
                                <input type="number" step="0.01" class="form-control @error('debt') is-invalid @enderror" wire:model="debt" placeholder="0.00" readonly>
                                @error('debt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Adresse') }}</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" wire:model="address" rows="3" placeholder="{{ __('Adresse du fournisseur') }}"></textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
