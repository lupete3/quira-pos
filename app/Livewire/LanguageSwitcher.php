<?php

namespace App\Livewire;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public $locale;
    protected $supportedLocales = ['fr', 'en', 'es', 'de']; 

    public function mount()
    {
        $this->locale = session('locale', config('app.locale'));
        app()->setLocale($this->locale);
    }

    public function changeLocale($locale)
    {
        if (in_array($locale, $this->supportedLocales)) {
            session()->put('locale', $locale);
            
            app()->setLocale($locale);
            $this->locale = $locale;
            $this->redirect(request()->header('Referer'), navigate: false);
        }
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
