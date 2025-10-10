<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="languageDropdown"
       role="button" data-bs-toggle="dropdown" aria-expanded="false">
        @if(app()->getLocale() === 'fr')
            <img src="{{ asset('assets/img/flags/fr.png') }}" alt="Français" width="20" class="me-1 rounded-circle">
            <span class="fw-semibold">FR</span>
        @else
            <img src="{{ asset('assets/img/flags/en.png') }}" alt="English" width="20" class="me-1 rounded-circle">
            <span class="fw-semibold">EN</span>
        @endif
    </a>

    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown" style="min-width: 130px;">
        <li>
            <a class="dropdown-item d-flex align-items-center" href="#" wire:click.prevent="changeLocale('fr')">
                <img src="{{ asset('assets/img/flags/fr.png') }}" alt="Français" width="18" class="me-2 rounded-circle">
                <span>{{ __('Français') }}</span>
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center" href="#" wire:click.prevent="changeLocale('en')">
                <img src="{{ asset('assets/img/flags/en.png') }}" alt="English" width="18" class="me-2 rounded-circle">
                <span>{{ __('English') }}</span>
            </a>
        </li>
    </ul>
</li>
