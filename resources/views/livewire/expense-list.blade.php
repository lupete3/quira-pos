<div>
    {{-- Recherche + Ajouter --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text"
                   class="form-control"
                   placeholder="{{ __('Rechercher des dépenses...') }}"
                   wire:model.live.debounce.300ms="search">
        </div>
        <button class="btn btn-primary"
                wire:click="create"
                data-bs-toggle="modal"
                data-bs-target="#expenseModal">
            <i class="bx bx-plus me-1"></i> {{ __('Ajouter') }}
        </button>
    </div>

    {{-- Tableau --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Catégorie') }}</th>
                    <th>{{ __('Magasin') }}</th>
                    <th>{{ __('Montant') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Statut') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $expense)
                    <tr wire:key="{{ $expense->id }}">
                        <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                        <td>{{ $expense->category->name ?? '-' }}</td>
                        <td>{{ $expense->store->name ?? '-' }}</td>
                        <td>{{ number_format($expense->amount, 2, ',', ' ') }} {{ company()->devise }}</td>
                        <td>{{ Str::limit($expense->description, 50) }}</td>
                        <td>
                          @switch($expense->status)
                              @case('pending')
                                  <span class="badge bg-label-warning me-1">En attente</span>
                              @break
                            @case('validated')
                                  <span class="badge bg-label-success me-1">Validé</span>
                              @break
                            @default
                                  <span class="badge bg-label-danger me-1">Annulé</span>
                          @endswitch
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button"
                                        class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    @if ($expense->status == 'pending')

                                    <a class="dropdown-item"
                                       href="#"
                                       wire:click="confirmValidate({{ $expense->id }}, 'validated')">
                                        <i class="bx bx-check me-1"></i> {{ __('Valider') }}
                                    </a>
                                    <a class="dropdown-item"
                                       href="#"
                                       wire:click="confirmValidate({{ $expense->id }}, 'cancelled')">
                                        <i class="bx bx-x me-1"></i> {{ __('Rejeter') }}
                                    </a>

                                    <a class="dropdown-item"
                                       href="#"
                                       wire:click="edit({{ $expense->id }})"
                                       data-bs-toggle="modal"
                                       data-bs-target="#expenseModal">
                                        <i class="bx bx-edit-alt me-1"></i> {{ __('Modifier') }}
                                    </a>
                                    <a class="dropdown-item"
                                       href="#"
                                       wire:click="confirmDelete({{ $expense->id }})">
                                        <i class="bx bx-trash me-1"></i> {{ __('Supprimer') }}
                                    </a>

                                    @else

                                    <a class="dropdown-item text-danger" href="#">
                                         {{ __('Aucune Action') }}
                                    </a>

                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('Aucune dépense trouvée.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $expenses->links() }}
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $isEditMode ? __('Modifier la dépense') : __('Nouvelle dépense') }}
                    </h5>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="{{ __('Fermer') }}"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="category_id" class="form-label">{{ __('Catégorie') }}</label>
                                <select id="category_id" class="form-control @error('category_id') is-invalid @enderror" wire:model="category_id">
                                    <option value="">{{ __('-- Sélectionner --') }}</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="store_id" class="form-label">{{ __('Magasin') }}</label>
                                <select id="store_id" class="form-control @error('store_id') is-invalid @enderror" wire:model="store_id">
                                    <option value="">{{ __('-- Sélectionner --') }}</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                                @error('store_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="amount" class="form-label">{{ __('Montant') }}</label>
                                <input type="number" step="0.01"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       wire:model="amount" id="amount">
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="date" class="form-label">{{ __('Date') }}</label>
                                <input type="date"
                                       class="form-control @error('date') is-invalid @enderror"
                                       wire:model="date" id="date">
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea id="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      wire:model="description"
                                      rows="3"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">{{ __('Fermer') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                            <i class="bx bx-check me-1"></i>
                            {{ $isEditMode ? __('Mettre à jour') : __('Enregistrer') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
