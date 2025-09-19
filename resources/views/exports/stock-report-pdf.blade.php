@extends('components.layouts.pdf')

@section('title', __('Rapport Stocks'))
@section('report-title', __('RAPPORT STOCK'))

@section('content')
    <h3><strong>Magasin :</strong>
      @if($store)
        {{ $store->name ?? '-' }}
      @else
        {{ __('Tous les magasins') }}
      @endif
    </h3>
    <table class="table">
        <thead class="table-light">
              <tr>
                <th>{{ __('Produit') }}</th>
                <th>{{ __('Catégorie') }}</th>
                <th>{{ __('Magasin') }}</th>
                <th>{{ __('Stock Actuel') }}</th>
                <th>{{ __('Valeur Achat') }}</th>
                <th>{{ __('Valeur Vente') }}</th>
                <th>{{ __('Bénéfice Attendu') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $prod)
                @php
                    $stock = $store
                        ? ($prod->stores->first()->pivot->quantity ?? 0)
                        : $prod->stores->sum('pivot.quantity');
                    $valeurAchat = $stock * $prod->purchase_price;
                    $valeurVente = $stock * $prod->sale_price;
                    $benefice = $valeurVente - $valeurAchat;
                @endphp
                <tr>
                    <td>{{ $prod->name }}</td>
                    <td>{{ $prod->category->name ?? '-' }}</td>
                    <td>
                        @if($store)
                            {{ $store->name ?? '-' }}
                        @else
                            {{ __('Tous magasins') }}
                        @endif
                    </td>
                    <td>{{ $stock }}</td>
                    <td>{{ number_format($valeurAchat, 2, ',', ' ') }} {{ company()->devise }}</td>
                    <td>{{ number_format($valeurVente, 2, ',', ' ') }} {{ company()->devise }}</td>
                    <td>{{ number_format($benefice, 2, ',', ' ') }} {{ company()->devise }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
