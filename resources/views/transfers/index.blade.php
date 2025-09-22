<x-layouts.app>
    <x-slot:title>
        {{ __('Historique des transferts') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Tous les transferts') }}</h5>
        </div>
        <div class="card-body">
            @livewire('transfer-list')
        </div>
    </div>
</x-layouts.app>
