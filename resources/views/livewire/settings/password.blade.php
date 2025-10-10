<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                // 'current_password' sera traduit par le système de validation de Laravel.
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
};
?>

@section('title', __('password.title'))

<section>
    @include('partials.settings-heading')

    <x-settings.layout :subheading="__('password.subheading')">
        <form wire:submit="updatePassword" class="w-50">
            <div class="mb-3">
                <label for="current_password" class="form-label">{{ __('password.current_password') }}</label>
                <input type="password" id="current_password" wire:model="current_password" class="form-control" required autocomplete="current-password" />
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">{{ __('password.new_password') }}</label>
                <input type="password" id="password" wire:model="password" class="form-control" required autocomplete="new-password" />
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">{{ __('password.confirm_password') }}</label>
                <input type="password" id="password_confirmation" wire:model="password_confirmation" class="form-control" required autocomplete="new-password" />
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary w-100">{{ __('password.save') }}</button>

                <x-action-message class="ms-3" on="password-updated">
                    {{ __('password.saved') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>