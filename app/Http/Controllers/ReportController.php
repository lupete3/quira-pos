<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display the products report view.
     */
    public function products()
    {
        return view('reports.products');
    }
    /**
     * Display the sales report view.
     */
    public function sales()
    {
        return view('reports.sales');
    }

    /**
     * Display the purchases report view.
     */
    public function purchases()
    {
        return view('reports.purchases');
    }

    /**
     * Display the customers report view.
     */
    public function customers()
    {
        return view('reports.customers');
    }

    /**
     * Display the Suppliers report view.
     */
    public function suppliers()
    {
        return view('reports.suppliers');
    }

    /**
     * Display the Suppliers report view.
     */
    public function stock()
    {
        return view('reports.stocks');
    }


    /**
     * Display the expenses report view.
     */
    public function expense()
    {
        return view('reports.expenses');
    }

    /**
     * Display the cashes report view.
     */
    public function cash()
    {
        return view('reports.cashs');
    }

    /**
     * Display the Profit/Loss report view.
     */
    public function prfitLoss()
    {
        return view('reports.profitloss');
    }

}
