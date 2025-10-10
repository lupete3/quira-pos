<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>{{ __('facture.receipt') }} #{{ $sale->id }}</title>
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
    .item-total { text-align:right; font-size:26px; font-weight:bold; margin-top: 10px; margin-bottom: 10px; }
    .footer { font-size:24px; text-align:center; margin-top:10px; }
  </style>
</head>
<body>

  <!-- En-tête -->
  <div class="center bold" style="font-size:36px;">{{ company()?->name ?? 'QUIRA POS' }}</div>
  <div class="center bold" style="font-size:25px;">{{ $sale->store?->name ?? company()?->name }}</div>
  <div class="center">{{ __(':rccm', ['rccm' => company()?->rccm]) }}</div>
  <div class="center">{{ __(':address', ['address' => $sale->store?->location ?? company()?->adress]) }}</div>
  <div class="center">{{ __(':phone', ['phone' => company()?->phone]) }}</div>
  <div class="line"></div>

  <!-- Titre et date -->
  <div class="center bold">{{ __('facture.sale_invoice') }}</div>
  <div class="center">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</div>
  <div class="line"></div>

  <!-- Infos client -->
  <div><strong>{{ __('facture.client') }} :</strong> {{ $client->name ?? __('facture.undefined') }}</div>
  <div><strong>{{ __('facture.phone') }} :</strong> {{ $client->phone ?? '-' }}</div>
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
    $tva = round(($sale->total_paid * 16 /100) , 2);
  @endphp

  <!-- Totaux -->
  <div class="item-qty-price">
    <span>{{ __('facture.price_ht') }} :</span><br>
    <span>{{ round(($sale->total_amount - $tva), 2) }}</span>
  </div>
  <div class="item-qty-price">
    <span>{{ __('facture.discount') }} :</span><br>
    <span>{{ round(($sale->discount ?? 0), 2) }}</span>
  </div>
  <div class="item-qty-price">
    <span>{{ __('facture.vat') }} :</span><br>
    <span>{{ round((($sale->total_paid * 16 /100) ?? 0), 2) }}</span>
  </div>

  <div class="item-qty-price">
    <span>{{ __('facture.price_ttc') }} :</span><br>
    <span>{{ round(($sale->total_amount + ($sale->discount ?? 0)), 2) }}</span>
  </div>
  <div class="item-total">
    <span>{{ __('facture.total_paid') }} :</span> {{ round($sale->total_paid, 2) }}{{ company()->devise ?? '' }}
  </div>
  <div class="item-total">
    <span>{{ __('facture.remaining') }} :</span> {{ round(($sale->total_amount - $sale->total_paid + ($sale->discount ?? 0)), 2) }}{{ company()->devise ?? '' }}
  </div>
  <p><strong>{{ __('facture.agent') }} :</strong> {{ $sale->user?->name ?? __('facture.undefined') }}</p>

  <div class="line"></div>
  <div class="center bold">{{ __('facture.thank_you') }}</div>

  <!-- Pied de page -->
  <div class="footer">
    {{ __('facture.footer_note') }}<br>
    {{ __('facture.footer_note2') }}
  </div>

  <script>
    window.onload = function(){ window.print(); }
  </script>
</body>
</html>