@extends('components.layouts.pdf')

@section('title', __('Rapport Fournisseurs'))
@section('report-title', __('RAPPORT FOURNISSEUR'))

@section('content')
    @if($date_from && $date_to)
        <p>{{ __('Période') }} : {{ $date_from }} {{ __('au') }} {{ $date_to }}</p>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Fournisseur') }}</th>
                <th>{{ __('Contact') }}</th>
                <th>{{ __('Nb Achats') }}</th>
                <th>{{ __('Total Achats') }}</th>
                <th>{{ __('Total Payé') }}</th>
                <th>{{ __('Solde') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suppliers as $supplier)
                @php
                    $totalAchats = $supplier->purchases->sum('total_amount');
                    $totalPaye = $supplier->purchases->sum('total_paid');
                    $solde = $totalAchats - $totalPaye;
                @endphp
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone }}</td>
                    <td>{{ $supplier->purchases->count() }}</td>
                    <td>{{ number_format($totalAchats, 2, ',', ' ') }} {{ company()?->devise }}</td>
                    <td>{{ number_format($totalPaye, 2, ',', ' ') }} {{ company()?->devise }}</td>
                    <td>{{ number_format($solde, 2, ',', ' ') }} {{ company()?->devise }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
