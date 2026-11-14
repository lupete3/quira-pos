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
                @if (Auth::user()->role_id != 3)<th>{{ __('raw_material.purchase_price') }}</th>@endif
                <th>{{ __('raw_material.total_stock') }}</th>
                @if (Auth::user()->role_id != 3)<th>{{ __('raw_material.total_value') }}</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach($rawMaterials as $material)
                <tr>
                    <td>{{ $material->designation }}</td>
                    @if (Auth::user()->role_id != 3)<td>{{ number_format($material->purchase_price, 2) }}</td>@endif
                    <td>{{ $material->stores->sum('pivot.quantity') }}</td>
                    @if (Auth::user()->role_id != 3)<td>{{ number_format($material->stores->sum('pivot.quantity') * $material->purchase_price, 2) }}</td>@endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection