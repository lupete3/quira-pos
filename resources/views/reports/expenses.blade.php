<x-layouts.app>
    <x-slot:title>
        {{ __('Rapport de dépenses') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Rapport de dépenses') }}</h5>
        </div>
        <div class="card-body">
            @livewire('reports.expense-report')
        </div>
    </div>
</x-layouts.app>
