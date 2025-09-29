@extends('components.layouts.pdf')

@section('title', __('Rapport de Caisse'))
@section('report-title', __('RAPPORT DE CAISSE'))

@section('content')
    <h4>{{ __('Magasin') }}: {{ $store ? $store->name : __('Tous les magasins') }}</h4>
    <h4>{{ __('Période') }}: {{ $start_date }} - {{ $end_date }}</h4>

    <!-- Résumé -->
    <table class="table mb-4" style="width:100%; border-collapse: collapse; text-align:center;">
        <thead>
            <tr>
                <th>{{ __('Total Entrées') }}</th>
                <th>{{ __('Total Dépenses') }}</th>
                <th>{{ __('Solde Net') }}</th>
                @if($current_balance !== null)
                    <th>{{ __('Solde Actuel en Caisse') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-success">
                    {{ number_format($total_in, 2) }} {{ company()?->devise }}
                </td>
                <td class="text-danger">
                    {{ number_format($total_out, 2) }} {{ company()?->devise }}
                </td>
                <td>
                    <strong>{{ number_format($net_balance, 2) }} {{ company()?->devise }}</strong>
                </td>
                @if($current_balance !== null)
                    <td>
                        <strong>{{ number_format($current_balance, 2) }} {{ company()?->devise }}</strong>
                    </td>
                @endif
            </tr>
        </tbody>
    </table>

    <!-- Détail des opérations -->
    <h5 class="mt-3">{{ __('Détails des opérations') }}</h5>
    <table class="table" style="width:100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Montant') }}</th>
                <th>{{ __('Utilisateur') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($transactions as $t)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>
                        @if($t->type === 'in')
                            <span class="text-success">{{ __('Entrée') }}</span>
                        @elseif($t->type === 'out')
                            <span class="text-danger">{{ __('Dépense') }}</span>
                        @elseif($t->type === 'opening')
                            <span class="text-primary">{{ __('Ouverture') }}</span>
                        @elseif($t->type === 'closing')
                            <span class="text-warning">{{ __('Décaissement') }}</span>
                        @endif
                    </td>
                    <td>{{ $t->description ?? '-' }}</td>
                    <td>
                        @if($t->type === 'out')
                            <span class="text-danger">-{{ number_format($t->amount, 2) }} {{ company()?->devise }}</span>
                        @else
                            <span class="text-success">{{ number_format($t->amount, 2) }} {{ company()?->devise }}</span>
                        @endif
                    </td>
                    <td>{{ $t->user?->name ?? '-' }}</td>
                    <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
