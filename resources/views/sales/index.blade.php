<x-layouts.app>
    <x-slot:title>
        {{ __('Historique des ventes') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Toutes les ventes') }}</h5>
        </div>
        <div class="card-body">
            @livewire('sale-list')
        </div>
    </div>
</x-layouts.app>
