@extends('components.layouts.pdf')

@section('title', __('Rapport Dépenses'))
@section('report-title', __('RAPPORT DÉPENSES'))

@section('content')
    <h4>{{ __('Magasin') }}: {{ $store ? $store->name : __('Tous les magasins') }}</h4>
    <h4>{{ __('Catégorie') }}: {{ $category ? $category->name : __('Toutes catégories') }}</h4>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Montant') }}</th>
                <th>{{ __('Catégorie') }}</th>
                <th>{{ __('Magasin') }}</th>
                <th>{{ __('Utilisateur') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_amount = 0;
            @endphp
            @foreach($expenses as $exp)
                @php
                    $total_amount += $exp->amount;
                @endphp
                <tr>
                    <td>{{ $exp->id }}</td>
                    <td>{{ $exp->description }}</td>
                    <td>{{ number_format($exp->amount, 2) }} {{ company()->devise }}</td>
                    <td>{{ $exp->category?->name ?? '-' }}</td>
                    <td>{{ $exp->store?->name ?? '-' }}</td>
                    <td>{{ $exp->user?->name ?? '-' }}</td>
                    <td>{{ $exp->expense_date }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-end">{{ __('Total Dépenses') }}</th>
                <th colspan="5" class="text-danger">
                    {{ number_format($total_amount, 2) }} {{ company()->devise }}
                </th>
            </tr>
        </tfoot>
    </table>
@endsection
