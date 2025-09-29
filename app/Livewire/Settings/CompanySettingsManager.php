<?php

namespace App\Livewire\Settings;


use App\Models\CompanySetting;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            'new_logo' => 'nullable|image|max:2048', // 2MB
            'devise'   => 'nullable|string|max:20',
        ]);

        if ($this->new_logo) {
            // ✅ Supprimer l'ancien logo s'il existe
            if ($this->company->logo && Storage::disk('public')->exists($this->company->logo)) {
                Storage::disk('public')->delete($this->company->logo);
            }

            // ✅ Enregistrer le nouveau logo
            $path = $this->new_logo->store('logos', 'public');
            $this->logo = $path;
        }

        $this->company->update([
            'tenant_id'    => Auth::user()?->tenant_id,
            'name'    => $this->name,
            'address' => $this->address,
            'email'   => $this->email,
            'phone'   => $this->phone,
            'rccm'    => $this->rccm,
            'id_nat'  => $this->id_nat,
            'logo'    => $this->logo,
            'devise'  => $this->devise,
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

