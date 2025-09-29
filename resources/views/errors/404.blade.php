{{-- resources/views/errors/404.blade.php --}}
<x-layouts.auth>
    <x-slot:title>
        {{ __('Page non trouvée') }}
    </x-slot:title>

    <div class="" style="height: calc(100vh - 85px)">
        <div class="misc-wrapper text-center">
            <h1 class="mb-2 mx-2" style="font-size: 6rem; font-weight: bold;">404</h1>
            <h4 class="mb-2">Oups 😢 Page introuvable !</h4>
            <p class="mb-4 mx-2">
                La page que vous cherchez n’existe pas ou a été déplacée.<br>
                Vérifiez l’URL ou retournez à l’accueil.
            </p>
            <a href="{{ url('/') }}" class="btn btn-primary">Retour à l'accueil</a>
        </div>
    </div>
</x-layouts.auth>

