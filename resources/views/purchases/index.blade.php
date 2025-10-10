<x-layouts.app>
    <x-slot:title>
        {{ __('purchase.purchase_history') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('purchase.all_purchases') }}</h5>
        </div>
        <div class="card-body">
            @livewire('purchase-list')
        </div>
    </div>
</x-layouts.app>
