<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des retours de ventes') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Retours de ventes') }}</h5>
        </div>
        <div class="card-body">
            @livewire('sale-return-list')
        </div>
    </div>
</x-layouts.app>
