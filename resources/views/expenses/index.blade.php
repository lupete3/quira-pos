<x-layouts.app>
    <x-slot:title>
        {{ __('expense.index_title') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('expense.title') }}</h5>
        </div>
        <div class="card-body">
            @livewire('expense-list')
        </div>
    </div>
</x-layouts.app>
