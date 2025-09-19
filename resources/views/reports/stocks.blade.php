<x-layouts.app>
    <x-slot:title>
        {{ __('Rapport de stock') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Rapport de stock') }}</h5>
        </div>
        <div class="card-body">
            @livewire('reports.stock-report')
        </div>
    </div>
</x-layouts.app>
