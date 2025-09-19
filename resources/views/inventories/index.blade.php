<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des Inventaires') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Inventaires') }}</h5>
        </div>
        <div class="card-body">
            @livewire('inventory-list')
        </div>
    </div>
</x-layouts.app>
