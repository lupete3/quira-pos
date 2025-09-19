<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Component
{
    public $locale;

    public function mount()
    {
        // Récupère la langue de session ou par défaut
        $this->locale = Session::get('locale', config('app.locale'));
        App::setLocale($this->locale);
    }

    public function updatedLocale($value)
    {
      dd($value);
        // Met à jour la session et la langue active
        Session::put('locale', $value);
        App::setLocale($value);

        // Émet un événement Livewire pour recharger tous les composants si nécessaire
        $this->dispatch('localeChanged');
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
