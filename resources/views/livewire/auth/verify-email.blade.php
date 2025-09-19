<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    /**
     * Envoyer un email de vérification à l’utilisateur.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            return;
        }

        Auth::user()->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Déconnecter l’utilisateur courant de l’application.
     */
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
};
?>

@section('title', __('Vérification de l’Email'))

@section('page-style')
@vite([
    'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

<div>
    <h4 class="mb-1">{{ __('Vérifiez votre email') }} 📧</h4>
    <p class="mb-6">{{ __('Veuillez vérifier votre adresse email en cliquant sur le lien que nous venons de vous envoyer.') }}</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4">
            {{ __('Un nouveau lien de vérification a été envoyé à l’adresse email fournie lors de l’inscription.') }}
        </div>
    @endif

    <div class="text-center mb-6">
        <button wire:click="sendVerification" class="btn btn-primary d-grid w-100 mb-3">
            {{ __('Renvoyer l’email de vérification') }}
        </button>

        <button wire:click="logout" class="btn btn-link">
            {{ __('Se déconnecter') }}
        </button>
    </div>
</div>
