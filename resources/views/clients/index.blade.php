<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des Clients') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Clients') }}</h5>
        </div>
        <div class="card-body">
            @livewire('client-list')
        </div>
    </div>
</x-layouts.app>
