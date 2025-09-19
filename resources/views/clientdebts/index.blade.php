<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des Dettes Clients') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Dettes Clients') }}</h5>
        </div>
        <div class="card-body">
            @livewire('client-debt-list')
        </div>
    </div>
</x-layouts.app>
