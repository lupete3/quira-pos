<x-layouts.app>
    <x-slot:title>
        {{ __('expense_category.index_title') }}
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('expense_category.title') }}</h5>
        </div>
        <div class="card-body">
            @livewire('expense-category-list')
        </div>
    </div>
</x-layouts.app>
