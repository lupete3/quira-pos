@extends('components.layouts.pdf')

@section('title', __('Rapport Inventaires'))
@section('report-title', __('RAPPORT INVENTAIRES'))

@section('content')
    <p><strong>{{ __('Date') }}:</strong> {{ $inventory->inventory_date }}</p>
    <p><strong>{{ __('Utilisateur') }}:</strong> {{ $inventory->user->name ?? __('N/A') }}</p>
    <p><strong>{{ __('Magasin') }}:</strong> {{ $inventory->store->name ?? __('N/A') }}</p>
    <p><strong>{{ __('Statut') }}:</strong> {{ $inventory->status }}</p>

    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Produit') }}</th>
                <th>{{ __('Stock Théorique') }}</th>
                <th>{{ __('Stock Physique') }}</th>
                <th>{{ __('Différence') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inventory->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? __('N/A') }}</td>
                    <td>{{ $item->theoretical_quantity }}</td>
                    <td>{{ $item->physical_quantity }}</td>
                    <td>
                        @if ($item->difference > 0)
                            <span class="badge bg-label-success">+{{ $item->difference }}</span>
                        @elseif($item->difference < 0)
                            <span class="badge bg-label-danger">{{ $item->difference }}</span>
                        @else
                            {{ $item->difference }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
