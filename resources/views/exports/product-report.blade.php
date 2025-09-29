@extends('components.layouts.pdf')

@section('title', __('Rapport Produits'))
@section('report-title', __('RAPPORT PRODUITS'))

@section('content')
    <h4>{{ __('Magasin') }}: {{ $store ? $store->name : __('Tous les magasins') }}</h4>
    {{-- Tableau produits --}}
    <table class="table table-bordered" width="100%" cellspacing="0" cellpadding="4" style="font-size: 9px;">
        <thead>
            <tr>
                <th>{{ __('Ref') }}</th>
                <th>{{ __('Nom') }}</th>
                <th>{{ __('Catégorie') }}</th>
                <th>{{ __('Marque') }}</th>
                <th>{{ __('Stock') }}</th>
                <th>{{ __('Prix Achat') }}</th>
                <th>{{ __('Prix Vente') }}</th>
                <th>{{ __('Valeur Stock') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $prod)
                @php
                    // Récupérer le stock du magasin sélectionné ou total
                    $storeStock = $store_id
                        ? ($prod->stores->where('id', $store_id)->first()->pivot->quantity ?? 0)
                        : $prod->stores->sum('pivot.quantity');
                @endphp
                <tr>
                    <td>{{ $prod->reference }}</td>
                    <td>{{ $prod->name }}</td>
                    <td>{{ $prod->category?->name ?? '-' }}</td>
                    <td>{{ $prod->brand?->name ?? '-' }}</td>
                    <td>{{ $storeStock }}</td>
                    <td>{{ number_format($prod->purchase_price, 2, ',', ' ') }} {{ company()?->devise }}</td>
                    <td>{{ number_format($prod->sale_price, 2, ',', ' ') }} {{ company()?->devise }}</td>
                    <td>{{ number_format($storeStock * $prod->sale_price, 2, ',', ' ') }} {{ company()?->devise }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
