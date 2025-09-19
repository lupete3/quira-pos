<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Client;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceController extends Controller
{
    public function print($saleId)
    {
        $sale = Sale::with(['client', 'items.product', 'user'])->findOrFail($saleId);
        $client = $sale->client;
        $items = $sale->items;
        $agent = $sale->user;

        return view('sales.print', compact('sale', 'client', 'items', 'agent'));
    }
}
