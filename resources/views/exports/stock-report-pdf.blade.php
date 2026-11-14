@extends('components.layouts.pdf')

@section('title', __('stockreport.report_title'))
@section('report-title', __('stockreport.report_title'))

@section('content')
    {{-- Information Magasin --}}
    <h3><strong>{{ __('stockreport.store') }}:</strong>
        @if($store)
            {{ $store->name ?? '-' }}
        @else
            {{ __('stockreport.all_stores') }}
        @endif
    </h3>
    
    {{-- Tableau de stock --}}
    <table class="table">
        <thead class="table-light">
            <tr>
                <th>{{ __('stockreport.product') }}</th>
                <th>{{ __('stockreport.category') }}</th>
                <th>{{ __('stockreport.store') }}</th>
                <th>{{ __('stockreport.current_stock') }}</th>
                @if (Auth::user()->role_id == 1)<th>{{ __('stockreport.purchase_value') }}</th>@endif
                <th>{{ __('stockreport.sale_value') }}</th>
                @if (Auth::user()->role_id != 3)<th>{{ __('stockreport.expected_profit') }}</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach($products as $prod)
                @php
                    $stock = $store
                        ? ($prod->stores->first()->pivot->quantity ?? 0)
                        : $prod->stores->sum('pivot.quantity');
                    $valeurVente = $stock * $prod->sale_price;
                    if (Auth::user()->role_id != 3) {
                        $valeurAchat = $stock * $prod->purchase_price;
                        $benefice = $valeurVente - $valeurAchat;
                    }
                @endphp
                <tr>
                    <td>{{ $prod->name }}</td>
                    <td>{{ $prod->category->name ?? '-' }}</td>
                    <td>
                        {{-- Affichage du magasin dans la ligne du tableau --}}
                        @if($store)
                            {{ $store->name ?? '-' }}
                        @else
                            {{ __('stockreport.all_stores') }}
                        @endif
                    </td>
                    <td>{{ $stock }}</td>
                    @if (Auth::user()->role_id == 1)<td>{{ number_format($valeurAchat, 2, ',', ' ') }} {{ company()?->devise }}</td>@endif
                    <td>{{ number_format($valeurVente, 2, ',', ' ') }} {{ company()?->devise }}</td>
                    @if (Auth::user()->role_id != 3)<td>{{ number_format($benefice, 2, ',', ' ') }} {{ company()?->devise }}</td>@endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align: right;">{{ __('Total') }}</th>
                @if (Auth::user()->role_id == 1)
                    <th>{{ number_format($totals['totalPurchaseValue'], 2, ',', ' ') }} {{ company()?->devise }}</th>
                @endif
                <th>{{ number_format($totals['totalSaleValue'], 2, ',', ' ') }} {{ company()?->devise }}</th>
                @if (Auth::user()->role_id != 3)
                    <th>{{ number_format($totals['totalExpectedProfit'], 2, ',', ' ') }} {{ company()?->devise }}</th>
                @endif
            </tr>
        </tfoot>
    </table>
@endsection