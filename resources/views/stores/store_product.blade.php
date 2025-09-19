<x-layouts.app>
    <x-slot:title>
        {{ __('Liste des produits par magasin') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Liste des produits') }}</h5>
        </div>
        <div class="card-body">
            @livewire('store-product-list', ['store' => $store])
        </div>
    </div>
</x-layouts.app>
