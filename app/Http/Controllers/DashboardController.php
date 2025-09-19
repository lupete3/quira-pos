<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientDebt;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function settings()
    {
        return view('settings.index');
    }
}
