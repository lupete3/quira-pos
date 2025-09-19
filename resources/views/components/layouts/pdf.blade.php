<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', __('Rapport'))</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 5px;
            color: #000;
        }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .table td, .table th { border: 1px solid #000; padding: 4px; }
        th { background-color: #7c8efe; }
        .badge {
            display: inline-block; padding: 2px 6px; border-radius: 4px;
            font-size: 9px; font-weight: bold;
        }
        .badge-success { background: #28a745; color: #fff; }
        .badge-danger { background: #dc3545; color: #fff; }
        .logo { width: 80px; }
        .section-title {
            margin-top: 10px; font-weight: bold; text-align: center; font-size: 11px;
        }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    {{-- HEADER ENTREPRISE --}}
    <div class="header" style="padding-bottom: 5px;">
        <table style="width:100%;">
            <tr>
                <td style="width: 15%;">
                    @if(company()?->logo && file_exists(public_path('storage/'.company()->logo)))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/'.company()->logo))) }}" class="logo" alt="{{ __('Logo') }}">
                    @else
                        <img src="{{ public_path('default-logo.png') }}" class="logo" alt="{{ __('Logo') }}">
                    @endif
                </td>
                <td style="width: 60%; text-align:center;">
                    <h2 style="margin: 0; font-size: 14px;">{{ strtoupper(company()?->name ?? config('app.name')) }}</h2>
                    <p style="margin: 0;">{{ __('Adresse') }} : {{ company()?->address ?? __('Non définie') }}</p>
                    <p style="margin: 0;">{{ __('Tel') }} : {{ company()?->phone ?? '-' }} – {{ __('Email') }} : {{ company()?->email ?? '-' }}</p>
                    @if(company()?->rccm || company()?->id_nat)
                        <p style="margin: 0;">{{ __('RCCM') }} : {{ company()?->rccm }} | {{ __('ID Nat') }} : {{ company()?->id_nat }}</p>
                    @endif
                </td>
                <td style="width: 25%; text-align:right; font-size: 9px;">
                    <strong>{{ __('Date') }} :</strong> {{ now()->format('d/m/Y') }}<br>
                    <strong>{{ __('Heure') }} :</strong> {{ now()->format('H:i') }}<br>
                    <strong>{{ __('Utilisateur') }} :</strong><br>
                    {{ auth()->user()->name ?? '-' }}
                </td>
            </tr>
        </table>
        <hr style="margin: 10px 0; border-bottom: 2px solid #5664e8;">
        <h3 class="text-center" style="text-decoration: underline; margin-bottom: 2px;">
            @yield('report-title', __('RAPPORT'))
        </h3>
    </div>

    {{-- CONTENU DU RAPPORT --}}
    <div>
        @yield('content')
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        {{ __('Rapport généré le') }} {{ now()->format('d/m/Y H:i') }} - {{ company()?->name ?? config('app.name') }}
    </div>

</body>
</html>
