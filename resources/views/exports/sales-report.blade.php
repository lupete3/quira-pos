@extends('components.layouts.pdf')

@section('title', __('Rapport Ventes'))
@section('report-title', __('RAPPORT VENTES'))

@section('content')
    <h4>{{ __('Magasin') }}: {{ $store ? $store->name : __('Tous les magasins') }}</h4>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Client') }}</th>
                <th>{{ __('Montant Total') }}</th>
                <th>{{ __('Payé') }}</th>
                <th>{{ __('Restant') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Magasin') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->client?->name ?? __('Client Ordinaire') }}</td>
                <td>{{ number_format($sale->total_amount, 2) }} {{ company()->devise }}</td>
                <td>{{ number_format($sale->total_paid, 2) }} {{ company()->devise }}</td>
                <td>{{ number_format($sale->total_amount - $sale->total_paid, 2) }} {{ company()->devise }}</td>
                <td>{{ $sale->store?->name ?? __('N/A') }}</td>
                <td>{{ $sale->sale_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
