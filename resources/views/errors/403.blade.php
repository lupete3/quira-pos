{{-- resources/views/errors/403.blade.php --}}
<x-layouts.auth>
    <x-slot:title>
        {{ __('messages.access_denied_title') }}
    </x-slot:title>

    <div style="height: calc(100vh - 85px)">
        <div class="misc-wrapper text-center">
            <h1 class="mb-2 mx-2" style="font-size: 6rem; font-weight: bold;">403</h1>
            <h4 class="mb-2">🚫 {{ __('messages.access_denied_heading') }}</h4>
            <p class="mb-4 mx-2">
                @isset($exception)
                    {{ $exception->getMessage() }}
                @else
                    {{ __('messages.access_denied_text') }}<br>
                    {{ __('messages.access_denied_tip') }}
                @endisset
            </p>
            <a href="{{ url('/') }}" class="btn btn-primary">{{ __('messages.back_to_home') }}</a>
        </div>
    </div>
</x-layouts.auth>
