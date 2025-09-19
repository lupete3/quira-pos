<x-layouts.app>
    <x-slot:title>
        {{ __('Paramètres de l\'Entreprise') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Paramètres de l\'Entreprise') }}</h5>
        </div>
        <div class="card-body">
            @livewire('settings.company-settings-manager')
        </div>
    </div>
</x-layouts.app>
