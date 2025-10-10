<x-layouts.app>
    <x-slot:title>
        {{ __('sale.sales_history') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('sale.all_sales') }}</h5>
        </div>
        <div class="card-body">
            @livewire('sale-list')
        </div>
    </div>
</x-layouts.app>
