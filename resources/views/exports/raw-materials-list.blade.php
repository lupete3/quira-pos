@extends('components.layouts.pdf')

@section('title', __('raw_material.list_title'))
@section('report-title', __('raw_material.list_title'))

@section('content')
    {{-- Filtre du rapport --}}
    <h4>{{ __('raw_material.list_title') }}</h4>
    <p>Date: {{ now()->format('d/m/Y H:i') }}</p>

    {{-- Tableau des ventes --}}
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('raw_material.designation') }}</th>
                <th>{{ __('raw_material.purchase_price') }}</th>
                <th>{{ __('raw_material.total_stock') }}</th>
                <th>{{ __('raw_material.total_value') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rawMaterials as $material)
                <tr>
                    <td>{{ $material->designation }}</td>
                    <td>{{ number_format($material->purchase_price, 2) }}</td>
                    <td>{{ $material->stores->sum('pivot.quantity') }}</td>
                    <td>{{ number_format($material->stores->sum('pivot.quantity') * $material->purchase_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection