<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des Marques') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Marques') }}</h5>
        </div>
        <div class="card-body">
            @livewire('brand-list')
        </div>
    </div>
</x-layouts.app>
