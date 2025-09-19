<x-layouts.app>
    <x-slot:title>
        {{ __('Gestion des Fournisseurs') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Fournisseurs') }}</h5>
        </div>
        <div class="card-body">
            @livewire('supplier-list')
        </div>
    </div>
</x-layouts.app>
