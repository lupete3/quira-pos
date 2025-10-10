<!-- Menu -->
<div wire:ignore>
  <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo" style="padding-top: 2rem; margin-bottom: 2rem;">
      @php
        $logoQuira = \App\Models\CompanySetting::first();
      @endphp
      @if($logoQuira?->logo && file_exists(public_path($logoQuira->logo)))
          <a href="{{ url('/') }}">
              <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path($logoQuira->logo))) }}"
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
          <div class="text-truncate">{{ __('menu.tableau_de_bord') }}</div>
        </a>
      </li>

      <!-- Magasin -->
      <li class="menu-item {{ request()->routeIs('pos.index') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('pos.index') }}" wire:navigate>
              <i class="menu-icon tf-icons bx bx-cart-alt"></i>
              <div class="text-truncate">{{ __('menu.magasin') }}</div>
          </a>
      </li>

      @if (Auth::user()->role_id == 1)

      <!-- Points de vente -->
      <li class="menu-item {{ request()->is('stores*') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('stores.index') }}" wire:navigate>
              <i class="menu-icon tf-icons bx bx-store"></i>
              <div class="text-truncate">{{ __('menu.points_de_vente') }}</div>
          </a>
      </li>

      <!-- Produits -->
      <li class="menu-item {{ request()->is('categories*') || request()->is('units*') || request()->is('brands*')
        || request()->is('products*') || request()->is('transfers*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-package"></i>
          <div class="text-truncate">{{ __('menu.produits') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->routeIs('categories.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('categories.index') }}" wire:navigate>{{ __('menu.categories') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('units.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('units.index') }}" wire:navigate>{{ __('menu.unites') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('brands.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('brands.index') }}" wire:navigate>{{ __('menu.marques') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('products.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('products.index') }}" wire:navigate>{{ __('menu.produits') }}</a>
          </li>
          {{-- <li class="menu-item {{ request()->routeIs('transfers.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('transfers.index') }}" wire:navigate>{{ __('menu.transfert_produits') }}</a>
          </li> --}}
        </ul>
      </li>

      @endif

      <!-- Contacts -->
      <li class="menu-item {{ request()->is('clients*') || request()->is('suppliers*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-user"></i>
          <div class="text-truncate">{{ __('menu.contacts') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->routeIs('clients.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('clients.index') }}" wire:navigate>{{ __('menu.clients') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('suppliers.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('suppliers.index') }}" wire:navigate>{{ __('menu.fournisseurs') }}</a>
          </li>
        </ul>
      </li>

      <!-- Ventes -->
      <li class="menu-item {{ request()->is('sales*') || request()->routeIs('salereturns.index') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-cart"></i>
          <div class="text-truncate">{{ __('menu.ventes') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->routeIs('sales.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('sales.index') }}" wire:navigate>{{ __('menu.historique') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('salereturns.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('salereturns.index') }}" wire:navigate>{{ __('menu.retours') }}</a>
          </li>
        </ul>
      </li>

      @if (Auth::user()->role_id == 1)

      <!-- Achats -->
      <li class="menu-item {{ request()->is('purchases*') || request()->routeIs('purchasereturns.index') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-cart-download"></i>
          <div class="text-truncate">{{ __('menu.achats') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->routeIs('purchases.create') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('purchases.create') }}" wire:navigate>{{ __('menu.nouvel_achat') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('purchases.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('purchases.index') }}" wire:navigate>{{ __('menu.historique') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('purchasereturns.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('purchasereturns.index') }}" wire:navigate>{{ __('menu.retours') }}</a>
          </li>
        </ul>
      </li>

      @endif

      <!-- Dettes -->
      <li class="menu-item {{ request()->routeIs('clientdebts.index') || request()->routeIs('supplierdebts.index') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-credit-card"></i>
          <div class="text-truncate">{{ __('menu.dettes') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->routeIs('clientdebts.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('clientdebts.index') }}" wire:navigate>{{ __('menu.clients') }}</a>
          </li>
          @if (Auth::user()->role_id == 1)
          <li class="menu-item {{ request()->routeIs('supplierdebts.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('supplierdebts.index') }}" wire:navigate>{{ __('menu.fournisseurs') }}</a>
          </li>
          @endif
        </ul>
      </li>

      @if (Auth::user()->role_id == 1)
      <!-- Inventaire -->
      <li class="menu-item {{ request()->routeIs('inventories*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('inventories.index') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-box"></i>
          <div class="text-truncate">{{ __('menu.inventaire') }}</div>
        </a>
      </li>
      @endif

      <!-- Dépenses -->
      <li class="menu-item {{ request()->routeIs('expensecategory.index') || request()->routeIs('expenses.index') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-wallet"></i>
          <div class="text-truncate">{{ __('menu.depenses') }}</div>
        </a>
        <ul class="menu-sub">
          @if (Auth::user()->role_id == 1)
          <li class="menu-item {{ request()->routeIs('expensecategory.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('expensecategory.index') }}" wire:navigate>{{ __('menu.categories_depense') }}</a>
          </li>
          @endif
          <li class="menu-item {{ request()->routeIs('expenses.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('expenses.index') }}" wire:navigate>{{ __('menu.depenses') }}</a>
          </li>
        </ul>
      </li>

      <!-- Rapports -->
      <li class="menu-item {{ request()->is('reports*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bxs-report"></i>
          <div class="text-truncate">{{ __('menu.rapports') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->routeIs('reports.products') ? 'active' : '' }}"><a class="menu-link" href="{{ route('reports.products') }}" wire:navigate>{{ __('menu.produits_rapport') }}</a></li>
          <li class="menu-item {{ request()->routeIs('reports.sales') ? 'active' : '' }}"><a class="menu-link" href="{{ route('reports.sales') }}" wire:navigate>{{ __('menu.ventes_rapport') }}</a></li>
          <li class="menu-item {{ request()->routeIs('reports.stock') ? 'active' : '' }}"><a class="menu-link" href="{{ route('reports.stock') }}" wire:navigate>{{ __('menu.stock') }}</a></li>
          @if (Auth::user()->role_id == 1)
          <li class="menu-item {{ request()->routeIs('reports.purchases') ? 'active' : '' }}"><a class="menu-link" href="{{ route('reports.purchases') }}" wire:navigate>{{ __('menu.achats_rapport') }}</a></li>
          <li class="menu-item {{ request()->routeIs('reports.customers') ? 'active' : '' }}"><a class="menu-link" href="{{ route('reports.customers') }}" wire:navigate>{{ __('menu.clients_rapport') }}</a></li>
          <li class="menu-item {{ request()->routeIs('reports.suppliers') ? 'active' : '' }}"><a class="menu-link" href="{{ route('reports.suppliers') }}" wire:navigate>{{ __('menu.fournisseurs_rapport') }}</a></li>
          @endif

          <li class="menu-item {{ request()->routeIs('reports.expense') ? 'active' : '' }}"><a class="menu-link" href="{{ route('reports.expense') }}" wire:navigate>{{ __('menu.depenses_rapport') }}</a></li>
          @if (Auth::user()->role_id == 1)
          <li class="menu-item {{ request()->routeIs('reports.cash') ? 'active' : '' }}"><a class="menu-link" href="{{ route('reports.cash') }}" wire:navigate>{{ __('menu.caisses') }}</a></li>
          {{-- <li class="menu-item {{ request()->routeIs('reports.profitloss') ? 'active' : '' }}"><a class="menu-link" href="{{ route('reports.profitloss') }}" wire:navigate>{{ __('menu.profits_pertes') }}</a></li> --}}
          @endif
        </ul>
      </li>

      @if (Auth::user()->role_id == 1)
      <!-- Utilisateurs -->
      <li class="menu-item {{ request()->routeIs('users*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('users.index') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-user-circle"></i>
          <div class="text-truncate">{{ __('menu.utilisateurs') }}</div>
        </a>
      </li>

      <!-- Paramètres -->
      <li class="menu-item {{ request()->is('settings/*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-cog"></i>
          <div class="text-truncate">{{ __('menu.parametres') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('settings.profile') }}" wire:navigate>{{ __('menu.profil') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('settings.password') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('settings.password') }}" wire:navigate>{{ __('menu.mot_de_passe') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('company.settings') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('company.settings') }}" wire:navigate>{{ __('menu.parametres_entreprise') }}</a>
          </li>
        </ul>
      </li>
      @endif

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
