<x-layouts.app>
    <x-slot:title>
        {{ __('Journal des Fournisseurs') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Journal des Fournisseurs') }}</h5>
        </div>
        <div class="card-body">
            @livewire('supplier-journal-list')
        </div>
    </div>
</x-layouts.app>
