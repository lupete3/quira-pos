<?php

namespace App\Livewire\Settings;


use App\Models\CompanySetting;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

use function Flasher\Notyf\Prime\notyf;

class CompanySettingsManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $company;
    public $name, $address, $email, $phone, $rccm, $id_nat, $logo, $new_logo, $devise;
    public $activeTab = 'settings';

    public function mount()
    {
        $this->company = CompanySetting::where('tenant_id', Auth::user()->tenant_id)->firstOrCreate([]);

        $this->fill([
            'name'    => $this->company->name,
            'address' => $this->company->address,
            'email'   => $this->company->email,
            'phone'   => $this->company->phone,
            'rccm'    => $this->company->rccm,
            'id_nat'  => $this->company->id_nat,
            'devise'  => $this->company->devise,
            'logo'    => $this->company->logo,
        ]);
    }

    public function save()
    {
        $this->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email',
            'phone'    => 'nullable|string|max:20',
            'new_logo' => 'nullable|image|max:2048',
            'devise'   => 'nullable|string|max:20',
        ]);

        if ($this->new_logo) {
            // 1. Définir le chemin de destination PUBLIC
            $destinationPath = public_path('logos');

            // 2. S'assurer que le dossier existe
            // Utilisation de File::isDirectory() et File::makeDirectory() est plus robuste
            if (!File::isDirectory($destinationPath)) {
                // Tente de créer le dossier récursivement. Mode 0755 est standard.
                if (!File::makeDirectory($destinationPath, 0755, true)) {
                    notyf()->error("Impossible de créer le dossier 'logos'. Vérifiez les permissions du dossier 'public'.");
                    return;
                }
            }

            // 3. Générer un nom unique et sécurisé
            $safeOriginalName = pathinfo($this->new_logo->getClientOriginalName(), PATHINFO_FILENAME);
            // Nettoyage: remplacer les caractères non sûrs par un tiret bas
            $safeOriginalName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $safeOriginalName);
            $extension = $this->new_logo->getClientOriginalExtension();
            $imageName = time() . '_' . $safeOriginalName . '.' . $extension;
            $fullDestination = $destinationPath . '/' . $imageName; // Chemin complet

            $success = false;

            try {
                $this->new_logo->move($destinationPath, $imageName);
                $success = true;
            } catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {

                // TENTER UNE MÉTHODE DE SECOURS (copy et delete)
                if (copy($this->new_logo->getRealPath(), $fullDestination)) {
                    // Si la copie réussit, supprimer l'original Livewire
                    if (File::delete($this->new_logo->getRealPath())) {
                        $success = true;
                    } else {
                        // La copie a réussi mais la suppression a échoué (un cas rare, mais important)
                        notyf()->error("Le logo a été copié, mais l'image temporaire n'a pas pu être supprimée. Veuillez contacter le support.");
                        // On considère l'opération réussie pour le chemin
                        $success = true;
                    }
                } else {
                    // Si move ET copy échouent, c'est clairement un problème de permissions
                    notyf()->error("Échec du déplacement et de la copie du fichier. Permission refusée pour le dossier: " . $destinationPath . ". Erreur: " . $e->getMessage());
                    return;
                }
            }

            if ($success) {
                // 5. Supprimer l'ancien logo APRÈS avoir vérifié que le nouveau a bien été enregistré
                if ($this->company->logo && File::exists(public_path($this->company->logo))) {
                    File::delete(public_path($this->company->logo));
                }

                // 6. Enregistrer le chemin RELATIF dans la DB
                $this->logo = 'logos/' . $imageName;
            } else {
                // Ce cas ne devrait pas arriver avec les try/catch/else, mais par sécurité.
                notyf()->error("Opération de sauvegarde du logo a échoué sans message d'erreur spécifique.");
                return;
            }
        }

        $this->company->update([
            'tenant_id' => Auth::user()?->tenant_id,
            'name'      => $this->name,
            'address'   => $this->address,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'rccm'      => $this->rccm,
            'id_nat'    => $this->id_nat,
            // Utiliser $this->logo si défini, sinon conserver l'ancien
            'logo'      => $this->logo ?? $this->company->logo,
            'devise'    => $this->devise,
        ]);

        notyf()->success(__('Paramètres mis à jour avec succès !'));
    }

    public function render()
    {
      $tenant = Auth::user()->tenant;

      $subscriptions = Subscription::with('plan')
        ->where('tenant_id', $tenant?->id)
        ->orderBy('start_date', 'desc')
        ->paginate(10);

      return view('livewire.settings.company-settings-manager', [
            'subscriptions' => $subscriptions,
      ]);
    }
}

