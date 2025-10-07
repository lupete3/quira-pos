<div>
    <div class="card mb-3">
        <div class="card-body row g-2 align-items-center ">

            <input type="text" wire:model.live="search" class="form-control" placeholder="{{ __('Rechercher un fournisseur...') }}">
            <div class="">
                <input type="date" wire:model.lazy="date_from" class="form-control mb-2">
                <input type="date" wire:model.lazy="date_to" class="form-control">
            </div>
            <div>
                <button wire:click="exportPdf" class="btn btn-danger">
                  <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                  <i class="bx bxs-file-pdf"></i> {{ __('Exporter PDF') }}
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Fournisseur') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Nb Achats') }}</th>
                        <th>{{ __('Total Achats') }}</th>
                        <th>{{ __('Total Payé') }}</th>
                        <th>{{ __('Solde') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        @php
                            $totalAchats = $supplier->purchases->sum('total_amount');
                            $totalPaye = $supplier->purchases->sum('total_paid');
                            $solde = $totalAchats - $totalPaye;
                        @endphp
                        <tr>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->phone }}</td>
                            <td>{{ $supplier->purchases->count() }}</td>
                            <td>{{ number_format($totalAchats, 2, ',', ' ') }} {{ company()?->devise }}</td>
                            <td>{{ number_format($totalPaye, 2, ',', ' ') }} {{ company()?->devise }}</td>
                            <td class="{{ $solde > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                {{ number_format($solde, 2, ',', ' ') }} {{ company()?->devise }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ __('Aucun résultat trouvé') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $suppliers->links() }}
    </div>
</div>
