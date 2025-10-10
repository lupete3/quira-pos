<?php

namespace App\Livewire;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public function changeLocale($locale)
    {
        if (in_array($locale, [ 'fr', 'en'])) {
            session()->put('locale', $locale);
            return $this->redirect(request()->header('Referer'));
        }
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
