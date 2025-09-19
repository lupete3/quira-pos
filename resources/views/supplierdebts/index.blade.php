<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des Dettes Fournisseurs') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Dettes Fournisseurs') }}</h5>
        </div>
        <div class="card-body">
            @livewire('supplier-debt-list')
        </div>
    </div>
</x-layouts.app>
