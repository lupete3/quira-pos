@extends('components.layouts.pdf')

@section('title', __('Rapport Achats'))
@section('report-title', __('RAPPORT ACHATS'))

@section('content')
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Fournisseur') }}</th>
                <th>{{ __('Montant Total') }}</th>
                <th>{{ __('Payé') }}</th>
                <th>{{ __('Restant') }}</th>
                <th>{{ __('Statut') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $purchase)
            <tr>
                <td>{{ $purchase->id }}</td>
                <td>{{ $purchase->supplier?->name ?? __('Fournisseur Libre') }}</td>
                <td>{{ number_format($purchase->total_amount, 2) }} {{ company()?->devise }}</td>
                <td>{{ number_format($purchase->total_paid, 2) }} {{ company()?->devise }}</td>
                <td>{{ number_format($purchase->total_amount - $purchase->total_paid, 2) }} {{ company()?->devise }}</td>
                <td>{{ ucfirst(__($purchase->status)) }}</td>
                <td>{{ $purchase->purchase_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
