<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des retours d\'achats') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Retours d\'achats') }}</h5>
        </div>
        <div class="card-body">
            @livewire('purchase-return-list')
        </div>
    </div>
</x-layouts.app>
