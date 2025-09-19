<div>
    {{-- Add button --}}
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('inventories.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> {{ __('Démarrer un nouvel inventaire') }}
        </a>
    </div>

    {{-- Inventories Table --}}
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Utilisateur') }}</th>
                    <th>{{ __('Magasin') }}</th>
                    <th>{{ __('Statut') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($inventories as $inventory)
                    <tr wire:key="{{ $inventory->id }}">
                        <td>{{ $inventory->id }}</td>
                        <td>{{ $inventory->inventory_date }}</td>
                        <td>{{ $inventory->store->name }}</td>
                        <td>{{ $inventory->user->name ?? __('N/A') }}</td>
                        <td>
                            <span class="badge bg-label-{{ $inventory->status == 'validated' ? 'success' : 'warning' }}">
                                {{ $inventory->status == 'validated' ? __('Validé') : __('En attente') }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('inventories.show', $inventory->id) }}" class="btn btn-info btn-sm">
                                <i class="bx bx-show me-1"></i> {{ __('Voir') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">{{ __('Aucun inventaire trouvé.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $inventories->links() }}
    </div>
</div>
