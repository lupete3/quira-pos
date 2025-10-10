<x-layouts.app>
    <x-slot:title>
        {{ __('store.store_management') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('store.stores') }}</h5>
        </div>
        <div class="card-body">
            @livewire('store-list')
        </div>
    </div>
</x-layouts.app>
