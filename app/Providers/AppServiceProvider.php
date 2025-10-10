<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Livewire::addPersistentMiddleware([
        // \App\Http\Middleware\SetLocale::class,
        // ]);

        // OU, si ta version Livewire ne supporte pas `addPersistentMiddleware`
        Livewire::listen('mount', function ($component) {
            App::setLocale(Session::get('locale', config('app.locale')));
        });
    }
}
