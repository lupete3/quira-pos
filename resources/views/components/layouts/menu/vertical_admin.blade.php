<!-- Menu -->
<div wire:ignore>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo" style="padding-top: 2rem; margin-bottom: 2rem;">
    @if(company()?->logo && file_exists(public_path('storage/'.company()->logo)))
        <a href="{{ url('/') }}" >
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/'.company()->logo))) }}"
                class="w-100" alt="{{ __('Logo') }}">
        </a>
    @else
        <a href="{{ url('/') }}" class="app-brand-link"><x-app-logo /></a>
    @endif
  </div>

  <div class="menu-inner-shadow mt-4"></div>

  <ul class="menu-inner py-1">

    <!-- Tableau de bord -->
    <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('dashboard') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-home"></i>
        <div class="text-truncate">{{ __('Tableau de Bord') }}</div>
      </a>
    </li>

    <!-- Clients -->
    <li class="menu-item {{ request()->routeIs('tenant.index') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('tenant.index') }}" wire:navigate>
            <i class="menu-icon tf-icons bx bx-user"></i> <!-- 👤 Icône clients -->
            <div class="text-truncate">{{ __('Clients') }}</div>
        </a>
    </li>

    <!-- Plan Abonnement -->
    <li class="menu-item {{ request()->routeIs('plan.index') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('plan.index') }}" wire:navigate>
            <i class="menu-icon tf-icons bx bx-package"></i> <!-- 📦 Icône plan -->
            <div class="text-truncate">{{ __('Plan Abonnement') }}</div>
        </a>
    </li>

    <!-- Souscription -->
    <li class="menu-item {{ request()->routeIs('souscription.index') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('souscription.index') }}" wire:navigate>
            <i class="menu-icon tf-icons bx bx-receipt"></i> <!-- 🧾 Icône souscription -->
            <div class="text-truncate">{{ __('Souscription') }}</div>
        </a>
    </li>


    <!-- Utilisateurs -->
    <li class="menu-item {{ request()->routeIs('users*') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('users.index') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-user-circle"></i>
        <div class="text-truncate">{{ __('Utilisateurs') }}</div>
      </a>
    </li>

    <!-- Paramètres -->
    <li class="menu-item {{ request()->is('settings/*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-cog"></i>
        <div class="text-truncate">{{ __('Paramètres') }}</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('settings.profile') }}" wire:navigate>{{ __('Profil') }}</a>
        </li>
        <li class="menu-item {{ request()->routeIs('settings.password') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('settings.password') }}" wire:navigate>{{ __('Mot de Passe') }}</a>
        </li>
        <li class="menu-item {{ request()->routeIs('company.settings') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('company.settings') }}" wire:navigate>{{ __('Paramètres Entreprise') }}</a>
        </li>
      </ul>
    </li>

  </ul>
</aside>

</div>
<!-- / Menu -->

<!-- Overlay (important pour mobile) -->
<div wire:ignore><div class="layout-overlay"></div></div>

<style>
  #layout-menu {
    max-height: 100vh;
    overflow-y: auto;
    overflow-x: hidden;
  }

  /* Optionnel : pour que le scroll soit plus élégant */
  #layout-menu::-webkit-scrollbar {
    width: 6px;
  }
  #layout-menu::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
  }
</style>

<script>
  // Toggle the 'open' class when the menu-toggle is clicked
  document.querySelectorAll('.menu-toggle').forEach(function(menuToggle) {
    menuToggle.addEventListener('click', function() {
      const menuItem = menuToggle.closest('.menu-item');
      // Toggle the 'open' class on the clicked menu-item
      menuItem.classList.toggle('open');
    });
  });
</script>
