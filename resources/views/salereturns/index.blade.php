<x-layouts.app>
    <x-slot:title>
        {{ __('sale_return.sales_return_management') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('sale_return.sales_returns') }}</h5>
        </div>
        <div class="card-body">
            @livewire('sale-return-list')
        </div>
    </div>
</x-layouts.app>