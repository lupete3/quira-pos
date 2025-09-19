<x-layouts.app>
    <x-slot:title>
        {{ __('Journal Client') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Journal Client') }}</h5>
        </div>
        <div class="card-body">
            @livewire('client-journal-list')
        </div>
    </div>
</x-layouts.app>
