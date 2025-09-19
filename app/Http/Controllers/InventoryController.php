<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('inventories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventories.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        return view('inventories.show', compact('inventory'));
    }

    /**
     * Display the specified resource.
     */
    public function export(Inventory $inventory)
    {
      $pdf = Pdf::loadView('exports.inventories-report', [
            'inventory' => $inventory
      ])->setPaper('a4', 'portrait');

      return response()->streamDownload(function() use ($pdf) {
          echo $pdf->output();
      }, "rapport-invtentaires.pdf");

    }
}
