
<x-layouts.app>
  <x-slot:title>
    Tableau de bord
  </x-slot:title>

  @if (Auth::user()->role_id == 4)
    <livewire:super-admin.dashboard />
  @else
    <livewire:dashboard.dashboard />
  @endif
  

</x-layouts.app>











