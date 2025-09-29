<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Store;
use App\Models\Role;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Carbon\Carbon;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // 1️⃣ Création du tenant
        $tenant = Tenant::create([
            'name'         => $this->name,
            'contact_name' => $this->name,
            'email'        => $this->email,
            'phone'        => null,
            'address'      => null,
            'is_active'    => true,
        ]);

        // 2️⃣ Création du magasin par défaut
        $store = Store::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Magasin Principal',
            'location'   => 'Adresse par défaut',
            'is_active' => true,
        ]);

        // 3️⃣ Rôle Admin (créé si inexistant)
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Administrateur du tenant']
        );

        // 4️⃣ Création du user (propriétaire)
        $user = User::create([
            'tenant_id' => $tenant->id,
            'store_id'  => $store->id,
            'role_id'   => $adminRole->id,
            'name'      => $this->name,
            'email'     => $this->email,
            'password'  => $validated['password'],
            'is_active' => true,
        ]);

        event(new Registered($user));

        // 5️⃣ Assigner une souscription gratuite
        $freePlan = Plan::where('price', 0)->first(); // ⚡ ton plan gratuit seedé
        if ($freePlan) {
            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id'   => $freePlan->id,
                'amount'     => 0,
                'start_date'=> Carbon::now(),
                'end_date'  => Carbon::now()->addDays($freePlan->duration_days),
                'status'    => 'active',
            ]);
        }

        // 6️⃣ Connexion auto
        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
};

?>

@section('title', __('Page d’inscription'))

@section('page-style')
@vite([
    'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

<div>
    <h4 class="mb-1">{{ __('L’aventure commence ici') }} 🚀</h4>
    <p class="mb-6">{{ __('Gérez votre application facilement et avec plaisir !') }}</p>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-info mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="register" class="mb-6">
        <div class="mb-6">
            <label for="name" class="form-label">{{ __('Nom') }}</label>
            <input
                wire:model="name"
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="name"
                required
                autofocus
                autocomplete="name"
                placeholder="{{ __('Entrez votre nom') }}"
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-6">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input
                wire:model="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                required
                autocomplete="email"
                placeholder="{{ __('Entrez votre email') }}"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-6 form-password-toggle">
            <label class="form-label" for="password">{{ __('Mot de passe') }}</label>
            <div class="input-group input-group-merge">
                <input
                    wire:model="password"
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    required
                    autocomplete="new-password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                >
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-6 form-password-toggle">
            <label class="form-label" for="password_confirmation">{{ __('Confirmer le mot de passe') }}</label>
            <div class="input-group input-group-merge">
                <input
                    wire:model="password_confirmation"
                    type="password"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    id="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                >
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-8">
            <div class="form-check mb-0 ms-2">
                <input wire:model="terms" type="checkbox" class="form-check-input @error('terms') is-invalid @enderror" id="terms">
                <label class="form-check-label" for="terms">
                    {{ __('J’accepte') }}
                    <a href="javascript:void(0);">{{ __('la politique de confidentialité et les conditions') }}</a>
                </label>
                @error('terms')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary d-grid w-100 mb-6">
            {{ __('S’inscrire') }}
        </button>
    </form>

    <p class="text-center">
        <span>{{ __('Vous avez déjà un compte ?') }}</span>
        <a href="{{ route('login') }}" wire:navigate>
            <span>{{ __('Connectez-vous ici') }}</span>
        </a>
    </p>
</div>
