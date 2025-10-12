<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.locale');
        
        if (Auth::check()) {
            $locale = Auth::user()->locale;
        } 
        elseif (session()->has('locale')) {
            $locale = session('locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}