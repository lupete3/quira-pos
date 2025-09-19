<x-layouts.app>
    <x-slot:title>
        {{ __('Rapport des fournisseurs') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Rapport des fournisseurs') }}</h5>
        </div>
        <div class="card-body">
            @livewire('reports.supplier-report')
        </div>
    </div>
</x-layouts.app>
