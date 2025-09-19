<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Initialiser le composant avec les informations de l'utilisateur actuel.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Mettre à jour les informations de profil de l'utilisateur connecté.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Renvoyer un email de vérification à l'utilisateur.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }
};
?>

@section('title', __('Profil'))

<section>
    @include('partials.settings-heading')

    <x-settings.layout :subheading="__('Mettez à jour votre nom et votre adresse email')">
        <form wire:submit="updateProfileInformation" class="mb-6 w-50">
            <div class="mb-4">
                <label for="name" class="form-label">{{ __('Nom') }}</label>
                <input type="text" id="name" wire:model="name" class="form-control" placeholder="{{ __('Jean Dupont') }}" required autofocus autocomplete="name">
            </div>

            <div class="mb-4">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <div class="input-group">
                    <input type="email" id="email" wire:model="email" class="form-control" placeholder="email@example.com" required autocomplete="email">
                    <span class="input-group-text">@example.com</span>
                </div>

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                    <div class="mt-3">
                        <p class="text-warning">
                            {{ __('Votre adresse email n\'est pas vérifiée.') }}
                            <a href="#" wire:click.prevent="resendVerificationNotification" class="text-info">{{ __('Cliquez ici pour renvoyer l\'email de vérification.') }}</a>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-success">
                                {{ __('Un nouveau lien de vérification a été envoyé à votre adresse email.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">{{ __('Enregistrer les modifications') }}</button>
                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Enregistré.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
