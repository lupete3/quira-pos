<x-layouts.app>
    <x-slot:title>
        {{ __('supplier_debt.index_title') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('supplier_debt.title') }}</h5>
        </div>
        <div class="card-body">
            @livewire('supplier-debt-list')
        </div>
    </div>
</x-layouts.app>
