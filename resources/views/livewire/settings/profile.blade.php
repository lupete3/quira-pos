<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

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

@section('title', __('profile.profile'))

<section>
    @include('partials.settings-heading')

    <x-settings.layout :subheading="__('profile.update_info')">

        <div class="d-flex justify-content-between">


            <form wire:submit="updateProfileInformation" class="mb-6 w-50">
                <div class="mb-4">
                    <label for="name" class="form-label">{{ __('profile.name') }}</label>
                    <input type="text" id="name" wire:model="name" class="form-control" placeholder="{{ __('profile.name_placeholder') }}" required autofocus autocomplete="name">
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label">{{ __('profile.email') }}</label>
                    <div class="input-group">
                        <input type="email" id="email" wire:model="email" class="form-control" placeholder="{{ __('profile.email_placeholder') }}" required autocomplete="email">
                    </div>

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                        <div class="mt-3">
                            <p class="text-warning">
                                {{ __('profile.email_not_verified') }}
                                <a href="#" wire:click.prevent="resendVerificationNotification" class="text-info">{{ __('profile.resend_verification') }}</a>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 text-success">
                                    {{ __('profile.verification_link_sent') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">{{ __('profile.save_changes') }}</button>
                    <x-action-message class="me-3" on="profile-updated">
                        {{ __('profile.saved') }}
                    </x-action-message>
                </div>
            </form>

            {{-- <livewire:settings.delete-user-form /> --}}

        </div>
    </x-settings.layout>
</section>