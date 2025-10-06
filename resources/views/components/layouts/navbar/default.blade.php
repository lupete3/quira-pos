<nav
  class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
  id="layout-navbar">
  <div wire:ignore>
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
      <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
        <i class="icon-base bx bx-menu icon-md"></i>
      </a>
    </div>
  </div>

  <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
    <!-- Search -->
    <div class="navbar-nav align-items-center me-auto ">
      <div class="nav-item d-flex align-items-center">
        <h3 class="text-primary mt-4">
          @php
            if(Auth::check()){
              if (Auth::user()->role_id == 1) {
                echo company()?->name ?? config('app.name');
              } else {
                $store = Auth::user()->stores()->first();
                if($store){
                  echo __('Point de Vente: ').$store?->name ?? company()?->name;
                }
              }
            } else{
              echo __('Quira POS');
            }
          @endphp
        </h3>
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-md-auto">
      <li class="nav-item lh-1 me-4">
        {{-- <select wire:model.lazy="localeL" class="form-select w-auto">
            <option value="fr">Français</option>
            <option value="en">English</option>
        </select> --}}
      </li>
      <!-- User -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        @if (Auth::check())
          <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
            <div class="avatar avatar-online">
              @if(company()?->logo && file_exists(public_path(company()->logo)))
                  <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path(company()->logo))) }}" class="w-px-40 h-auto rounded-circle" alt="{{ __('Logo') }}">
              @else
                  <img src="{{ Auth::user()->profile_photo_url ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
              @endif
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="{{ route('settings.profile') }}" wire:navigate>
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-online">
                      @if(company()?->logo && file_exists(public_path(company()->logo)))
                          <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path(company()->logo))) }}" class="w-px-40 h-auto rounded-circle" alt="{{ __('Logo') }}">
                      @else
                          <img src="{{ Auth::user()->profile_photo_url ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                      @endif
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-0">{{ Auth::user()?->name }}</h6>
                    <small class="text-body-secondary">{{ Auth::user()->role?->name ?? __('Utilisateur') }}</small>
                  </div>
                </div>
              </a>
            </li>
            <li>
              <div class="dropdown-divider my-1"></div>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}" href="{{ route('settings.profile') }}" wire:navigate>
                <i class="icon-base bx bx-user icon-md me-3"></i><span>{{ __('Mon Profil') }}</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('settings.password') ? 'active' : '' }}" href="{{ route('settings.password') }}" wire:navigate>
                <i class="icon-base bx bx-cog icon-md me-3"></i><span>{{ __('Paramètres') }}</span>
              </a>
            </li>
            <li>
              <div class="dropdown-divider my-1"></div>
            </li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="dropdown-item" type="submit" class="btn p-0">
                  <i class="icon-base bx bx-power-off icon-md me-3"></i><span>{{ __('Se Déconnecter') }}</span>
                </button>
              </form>
            </li>
          </ul>
        @else
          <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
            <div class="avatar avatar-online">
              <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('login') }}">{{ __('Se Connecter') }}</a></li>
          </ul>
        @endif
      </li>
      <!--/ User -->
    </ul>
  </div>
</nav>

<!-- ========== Script pour activer le menu mobile ========== -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.querySelector(".layout-menu-toggle a");
    const body = document.body;
    const overlay = document.querySelector(".layout-overlay");

    if (toggleBtn) {
      toggleBtn.addEventListener("click", function () {
        body.classList.toggle("layout-menu-expanded");
      });
    }

    if (overlay) {
      overlay.addEventListener("click", function () {
        body.classList.remove("layout-menu-expanded");
      });
    }
  });
</script>
