<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des Points de vente') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ trans('store.stores') }}</h5>
        </div>
        <div class="card-body">
            @livewire('store-list')
        </div>
    </div>
</x-layouts.app>
