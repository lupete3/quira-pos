@extends('components.layouts.pdf')

@section('title', __('raw_material.report_title'))
@section('report-title', __('raw_material.report_title'))

@section('content')
    {{-- Filtre du rapport --}}
    <h4>{{ __('raw_material.report_title') }}</h4>

    {{-- Tableau des ventes --}}
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('raw_material.date') }}</th>
                <th>{{ __('raw_material.store') }}</th>
                <th>{{ __('raw_material.designation') }}</th>
                <th>{{ __('raw_material.type') }}</th>
                <th>{{ __('raw_material.quantity') }}</th>
                <th>{{ __('raw_material.total_value') }}</th>
                <th>{{ __('raw_material.user') }}</th>
                <th>{{ __('raw_material.description_reason') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $transaction->store->name }}</td>
                    <td>{{ $transaction->rawMaterial->designation }}</td>
                    <td>
                        @if($transaction->type === 'entry')
                            <span class="text-success">{{ __('raw_material.entry_stock') }}</span>
                        @else
                            <span class="text-danger">{{ __('raw_material.exit_stock') }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="{{ $transaction->type === 'entry' ? 'text-success' : 'text-danger' }}">
                            {{ $transaction->type === 'entry' ? '+' : '-' }}{{ $transaction->quantity }}
                        </span>
                    </td>
                    <td>{{ number_format($transaction->quantity * $transaction->rawMaterial->purchase_price, 2) }}</td>
                    <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                    <td>{{ $transaction->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection