<div>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">{{ __('raw_material.store') }}</label>
                    <select wire:model.live="store_id" class="form-select">
                        <option value="">{{ __('raw_material.all_stores') }}</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">{{ __('raw_material.raw_material') }}</label>
                    <select wire:model.live="raw_material_id" class="form-select">
                        <option value="">{{ __('raw_material.all') }}</option>
                        @foreach($rawMaterials as $rm)
                            <option value="{{ $rm->id }}">{{ $rm->designation }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">{{ __('raw_material.from_date') }}</label>
                    <input type="date" wire:model.live="date_from" class="form-control">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">{{ __('raw_material.to_date') }}</label>
                    <input type="date" wire:model.live="date_to" class="form-control">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" wire:click="export" wire:target="export"
                        wire:loading.attr="disabled">
                        <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                        <i class="bx bx-download"></i> {{ __('raw_material.export_pdf') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('raw_material.date') }}</th>
                        <th>{{ __('raw_material.store') }}</th>
                        <th>{{ __('raw_material.designation') }}</th>
                        <th>{{ __('raw_material.type') }}</th>
                        <th>{{ __('raw_material.quantity') }}</th>
                        <th>{{ __('raw_material.total_value') }}</th>
                        <th>{{ __('raw_material.user') }}</th>
                        <th>{{ __('raw_material.description') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $transaction->store->name }}</td>
                            <td><strong>{{ $transaction->rawMaterial->designation }}</strong></td>
                            <td>
                                @if($transaction->type === 'entry')
                                    <span class="badge bg-label-success">{{ __('raw_material.entry') }}</span>
                                @else
                                    <span class="badge bg-label-danger">{{ __('raw_material.exit') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="{{ $transaction->type === 'entry' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type === 'entry' ? '+' : '-' }}{{ $transaction->quantity }}
                                </span>
                            </td>
                            <td>
                                {{ number_format($transaction->quantity * $transaction->rawMaterial->purchase_price, 2) }}
                            </td>
                            <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                            <td>{{ $transaction->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('raw_material.no_movements_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
    </div>
</div>