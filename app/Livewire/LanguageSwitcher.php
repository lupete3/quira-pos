<?php

namespace App\Livewire;

use App\Models\Language;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LanguageSwitcher extends Component
{
    public $locale;
    protected $supportedLocales = ['fr', 'en', 'es', 'de']; // Adaptez à vos besoins

    public function mount()
    {
        // Si l'utilisateur est authentifié, utiliser la locale de la base de données.
        if (Auth::check()) {
            $this->locale = Auth::user()->locale;
        } else {
            // Sinon (visiteur), revenir à la session ou la config par défaut
            $this->locale = session('locale', config('app.locale'));
        }
        
        // Appliquer la locale lue à l'application
        app()->setLocale($this->locale);
    }

    public function changeLocale($locale)
    {
        if (in_array($locale, $this->supportedLocales)) {
            
            // 1. Mise à jour de la base de données si l'utilisateur est connecté
            if (Auth::check()) {
                Language::updateOrCreate(
                    ['user_id' => Auth::id()],
                    ['locale' => $locale]
                );
            } else {
                // Pour les visiteurs, on conserve la session
                session()->put('locale', $locale);
            }
            
            // 2. Met à jour la locale de l'application et la propriété Livewire
            app()->setLocale($locale);
            $this->locale = $locale;

            $this->dispatch('updateLocale', locale: $locale);

            // 4️⃣ Actualise les autres composants Livewire
            $this->dispatch('localeUpdatedGlobally', locale: $locale);

            notyf()->success(__('dashboard.language_change'));
            
            // 3. Forcer le rechargement pour que tout le framework applique le changement
            $this->redirect(request()->header('Referer'), navigate: false);
        }
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}

