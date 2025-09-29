{{-- resources/views/errors/403.blade.php --}}
<x-layouts.auth>
    <x-slot:title>
        {{ __('Accès refusé') }}
    </x-slot:title>

    <div style="height: calc(100vh - 85px)">
        <div class="misc-wrapper text-center">
            <h1 class="mb-2 mx-2" style="font-size: 6rem; font-weight: bold;">403</h1>
            <h4 class="mb-2">🚫 Oups ! Accès interdit</h4>
            <p class="mb-4 mx-2">
                @isset($exception)
                    {{ $exception->getMessage() }}
                @else
                    Vous n’avez pas l’autorisation d’accéder à cette page.<br>
                    Contactez votre responsable ou retournez à l’accueil.
                @endisset
            </p>
            <a href="{{ url('/') }}" class="btn btn-primary">Retour à l'accueil</a>
        </div>
    </div>
</x-layouts.auth>