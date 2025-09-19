<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>{{ __('Reçu #') }}{{ $sale->id }}</title>
  <style>
    @media print {
      @page { margin:0; }
      body { margin:0; padding:0; font-family:'Courier New', monospace; font-size:28px; line-height:1.3; }
    }
    body {
      margin:0;
      font-family:'Courier New', monospace;
      font-size:28px;
      line-height:1.3;
      padding:5px 10px;
      max-width:400px;
    }
    .center { text-align:center; }
    .bold { font-weight:bold; }
    .line { border-top:2px dashed #000; margin:5px 0; }
    .item-block { margin-bottom:10px; border-bottom:1px dashed #000; padding-bottom:5px; }
    .item-name { font-weight:bold; font-size:20px; }
    .item-qty-price { display:flex; justify-content:space-between; font-size:26px; }
    .item-total { text-align:right; font-size:26px; font-weight:bold; }
    .footer { font-size:24px; text-align:center; margin-top:10px; }
  </style>
</head>
<body>

  <!-- En-tête -->
  <div class="center bold" style="font-size:36px;">{{ company()?->name }}</div>
  <div class="center">{{ __('N° RCCM: :rccm', ['rccm' => company()?->rccm]) }}</div>
  <div class="center">{{ __('Adresse: :address', ['address' => company()?->adress]) }}</div>
  <div class="center">{{ __('Tél: :phone', ['phone' => company()?->phone]) }}</div>
  <div class="line"></div>

  <!-- Titre et date -->
  <div class="center bold">{{ __('FACTURE DE VENTE') }}</div>
  <div class="center">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</div>
  <div class="line"></div>

  <!-- Infos client -->
  <div><strong>{{ __('Client') }} :</strong> {{ $client->name ?? __('Non défini') }}</div>
  <div><strong>{{ __('Tél') }} :</strong> {{ $client->telephone ?? '-' }}</div>
  <div class="line"></div>

  <!-- Blocs des produits -->
  @foreach($sale->items as $item)
  <div class="item-block">
    <div class="item-name">{{ $item->product->name }}</div>
    <div class="item-qty-price">
      <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 2, ',', ' ') }}</span>
      <span>{{ number_format($item->total_price, 2, ',', ' ') }}</span>
    </div>
  </div>
  @endforeach

  <div class="line"></div>

  @php
    $tva = number_format(($sale->total_paid * 16 /100) , 2);
  @endphp

  <!-- Totaux -->
  <div class="item-qty-price">
    <span>{{ __('Prix HT') }} :</span><br>
    <span>{{ number_format($sale->total_amount - $tva, 2, ',', ' ') }}</span>
  </div>
  <div class="item-qty-price">
    <span>{{ __('Reduction') }} :</span><br>
    <span>{{ number_format($sale->discount ?? 0, 2, ',', ' ') }}</span>
  </div>
  <div class="item-qty-price">
    <span>{{ __('TVA (16%)') }} :</span><br>
    <span>{{ number_format(($sale->total_paid * 16 /100) ?? 0, 2, ',', ' ') }}</span>
  </div>
  
  <div class="item-qty-price">
    <span>{{ __('Prix TTC') }} :</span><br>
    <span>{{ number_format($sale->total_amount + ($sale->discount ?? 0), 2, ',', ' ') }}</span>
  </div>
  <div class="item-total">
    <span>{{ __('Total payé') }} :</span> {{ number_format($sale->total_paid, 2, ',', ' ') }}{{ company()->devise ?? '' }}
  </div>

  <div class="line"></div>
  <div class="center bold">{{ __('Merci pour votre confiance !') }}</div>

  <!-- Pied de page -->
  <div class="footer">
    {{ __('Ce document fait office de facture.') }}<br>
    {{ __('Aucun remboursement sans ce reçu.') }}
  </div>

  <script>
    window.onload = function(){ window.print(); }
  </script>
</body>
</html>
