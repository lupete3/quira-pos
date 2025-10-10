<x-layouts.app>
    <x-slot:title>
        {{ __('purchase_return.purchase_return_management') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('purchase_return.purchase_returns') }}</h5>
        </div>
        <div class="card-body">
            @livewire('purchase-return-list')
        </div>
    </div>
</x-layouts.app>
