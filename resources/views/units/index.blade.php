<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des Unités') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Unités') }}</h5>
        </div>
        <div class="card-body">
            @livewire('unit-list')
        </div>
    </div>
</x-layouts.app>

