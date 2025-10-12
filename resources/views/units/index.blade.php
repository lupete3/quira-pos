<x-layouts.app>
    <x-slot:title>
        {{ __('unit.index_title') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('unit.title') }}</h5>
        </div>
        <div class="card-body">
            @livewire('unit-list')
        </div>
    </div>
</x-layouts.app>

