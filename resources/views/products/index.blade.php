<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des produits') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Produits') }}</h5>
        </div>
        <div class="card-body">
            @livewire('product-list')
        </div>
    </div>
</x-layouts.app>
