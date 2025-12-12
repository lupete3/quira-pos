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
      {{-- <li class="menu-item">
        <p class="menu-link text-primary">
          @php
          if(Auth::check()){
          if (Auth::user()->role_id == 1) {
          echo company()?->name ?? config('app.name');
          } else {
          $store = Auth::user()->stores()->first();
          if($store){
          echo __('navbar.point_de_vente: ').$store?->name ?? company()?->name;
          }
          }
          } else{
          echo __('navbar.application_name');
          }
          @endphp
        </p>
      </li> --}}

      <!-- Tableau de bord -->
      <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('dashboard') }}">
          <i class="menu-icon tf-icons bx bx-home"></i>
          <div class="text-truncate">{{ __('menu.tableau_de_bord') }}</div>
        </a>
      </li>

      <!-- Magasin -->
      <li class="menu-item {{ request()->routeIs('pos.index') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('pos.index') }}">
          <i class="menu-icon tf-icons bx bx-cart-alt"></i>
          <div class="text-truncate">{{ __('menu.magasin') }}</div>
        </a>
      </li>

      <!-- Matières Premières -->
      <li class="menu-item {{ request()->routeIs('rawmaterials.index') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('rawmaterials.index') }}">
          <i class="menu-icon tf-icons bx bx-cube"></i>
          <div class="text-truncate">{{ __('menu.matieres_premieres') }}</div>
        </a>
      </li>

      @if (Auth::user()->role_id == 1)

          <!-- Points de vente -->
          <li class="menu-item {{ request()->is('stores*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('stores.index') }}">
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
                <a class="menu-link" href="{{ route('categories.index') }}">{{ __('menu.categories') }}</a>
              </li>
              <li class="menu-item {{ request()->routeIs('units.index') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('units.index') }}">{{ __('menu.unites') }}</a>
              </li>
              <li class="menu-item {{ request()->routeIs('brands.index') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('brands.index') }}">{{ __('menu.marques') }}</a>
              </li>
              <li class="menu-item {{ request()->routeIs('products.index') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('products.index') }}">{{ __('menu.produits') }}</a>
              </li>
              {{-- <li class="menu-item {{ request()->routeIs('transfers.index') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('transfers.index') }}">{{ __('menu.transfert_produits') }}</a>
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
            <a class="menu-link" href="{{ route('clients.index') }}">{{ __('menu.clients') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('suppliers.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('suppliers.index') }}">{{ __('menu.fournisseurs') }}</a>
          </li>
        </ul>
      </li>

      <!-- Ventes -->
      <li
        class="menu-item {{ request()->is('sales*') || request()->routeIs('salereturns.index') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-cart"></i>
          <div class="text-truncate">{{ __('menu.ventes') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->routeIs('sales.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('sales.index') }}">{{ __('menu.historique') }}</a>
          </li>
          <li class="menu-item {{ request()->routeIs('salereturns.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('salereturns.index') }}">{{ __('menu.retours') }}</a>
          </li>
        </ul>
      </li>

      @if (Auth::user()->role_id == 1)

        <!-- Achats -->
        <li
          class="menu-item {{ request()->is('purchases*') || request()->routeIs('purchasereturns.index') ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-cart-download"></i>
            <div class="text-truncate">{{ __('menu.achats') }}</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('purchases.create') ? 'active' : '' }}">
              <a class="menu-link" href="{{ route('purchases.create') }}">{{ __('menu.nouvel_achat') }}</a>
            </li>
            <li class="menu-item {{ request()->routeIs('purchases.index') ? 'active' : '' }}">
              <a class="menu-link" href="{{ route('purchases.index') }}">{{ __('menu.historique') }}</a>
            </li>
            <li class="menu-item {{ request()->routeIs('purchasereturns.index') ? 'active' : '' }}">
              <a class="menu-link" href="{{ route('purchasereturns.index') }}">{{ __('menu.retours') }}</a>
            </li>
          </ul>
        </li>

      @endif

      <!-- Dettes -->
      <li
        class="menu-item {{ request()->routeIs('clientdebts.index') || request()->routeIs('supplierdebts.index') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-credit-card"></i>
          <div class="text-truncate">{{ __('menu.dettes') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->routeIs('clientdebts.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('clientdebts.index') }}">{{ __('menu.clients') }}</a>
          </li>
          @if (Auth::user()->role_id == 1)
            <li class="menu-item {{ request()->routeIs('supplierdebts.index') ? 'active' : '' }}">
              <a class="menu-link" href="{{ route('supplierdebts.index') }}">{{ __('menu.fournisseurs') }}</a>
            </li>
          @endif
        </ul>
      </li>

      @if (Auth::user()->role_id == 1)
        <!-- Inventaire -->
        <li class="menu-item {{ request()->routeIs('inventories*') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('inventories.index') }}">
            <i class="menu-icon tf-icons bx bx-box"></i>
            <div class="text-truncate">{{ __('menu.inventaire') }}</div>
          </a>
        </li>
      @endif

      <!-- Dépenses -->
      <li
        class="menu-item {{ request()->routeIs('expensecategory.index') || request()->routeIs('expenses.index') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-wallet"></i>
          <div class="text-truncate">{{ __('menu.depenses') }}</div>
        </a>
        <ul class="menu-sub">
          @if (Auth::user()->role_id == 1)
            <li class="menu-item {{ request()->routeIs('expensecategory.index') ? 'active' : '' }}">
              <a class="menu-link" href="{{ route('expensecategory.index') }}">{{ __('menu.categories_depense') }}</a>
            </li>
          @endif
          <li class="menu-item {{ request()->routeIs('expenses.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('expenses.index') }}">{{ __('menu.depenses') }}</a>
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
          <li class="menu-item {{ request()->routeIs('reports.products') ? 'active' : '' }}"><a class="menu-link"
              href="{{ route('reports.products') }}">{{ __('menu.produits_rapport') }}</a></li>
          <li class="menu-item {{ request()->routeIs('reports.sales') ? 'active' : '' }}"><a class="menu-link"
              href="{{ route('reports.sales') }}">{{ __('menu.ventes_rapport') }}</a></li>
          <li class="menu-item {{ request()->routeIs('reports.stock') ? 'active' : '' }}"><a class="menu-link"
              href="{{ route('reports.stock') }}">{{ __('menu.stock') }}</a></li>
          <li class="menu-item {{ request()->routeIs('reports.rawmaterials') ? 'active' : '' }}"><a class="menu-link"
              href="{{ route('reports.rawmaterials') }}">{{ __('menu.row_material_report') }}</a></li>
          @if (Auth::user()->role_id == 1)
            <li class="menu-item {{ request()->routeIs('reports.purchases') ? 'active' : '' }}"><a class="menu-link"
                href="{{ route('reports.purchases') }}">{{ __('menu.achats_rapport') }}</a></li>
            <li class="menu-item {{ request()->routeIs('reports.customers') ? 'active' : '' }}"><a class="menu-link"
                href="{{ route('reports.customers') }}">{{ __('menu.clients_rapport') }}</a></li>
            <li class="menu-item {{ request()->routeIs('reports.suppliers') ? 'active' : '' }}"><a class="menu-link"
                href="{{ route('reports.suppliers') }}">{{ __('menu.fournisseurs_rapport') }}</a></li>
          @endif

          <li class="menu-item {{ request()->routeIs('reports.expense') ? 'active' : '' }}"><a class="menu-link"
              href="{{ route('reports.expense') }}">{{ __('menu.depenses_rapport') }}</a></li>
          @if (Auth::user()->role_id == 1)
            <li class="menu-item {{ request()->routeIs('reports.cash') ? 'active' : '' }}"><a class="menu-link"
                href="{{ route('reports.cash') }}">{{ __('menu.caisses') }}</a></li>
            {{-- <li class="menu-item {{ request()->routeIs('reports.profitloss') ? 'active' : '' }}"><a class="menu-link"
                href="{{ route('reports.profitloss') }}">{{ __('menu.profits_pertes') }}</a></li> --}}
          @endif
        </ul>
      </li>

      @if (Auth::user()->role_id == 1)
        <!-- Utilisateurs -->
        <li class="menu-item {{ request()->routeIs('users*') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('users.index') }}">
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
              <a class="menu-link" href="{{ route('settings.profile') }}">{{ __('menu.profil') }}</a>
            </li>
            <li class="menu-item {{ request()->routeIs('settings.password') ? 'active' : '' }}">
              <a class="menu-link" href="{{ route('settings.password') }}">{{ __('menu.mot_de_passe') }}</a>
            </li>
            <li class="menu-item {{ request()->routeIs('company.settings') ? 'active' : '' }}">
              <a class="menu-link" href="{{ route('company.settings') }}">{{ __('menu.parametres_entreprise') }}</a>
            </li>
          </ul>
        </li>
      @endif

    </ul>
  </aside>
</div>
<!-- / Menu -->

<!-- Overlay (important pour mobile) -->
<div wire:ignore>
  <div class="layout-overlay"></div>
</div>

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
  document.querySelectorAll('.menu-toggle').forEach(function (menuToggle) {
    menuToggle.addEventListener('click', function () {
      const menuItem = menuToggle.closest('.menu-item');
      // Toggle the 'open' class on the clicked menu-item
      menuItem.classList.toggle('open');
    });
  });
</script>
