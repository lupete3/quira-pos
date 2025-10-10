<?php

namespace App\Livewire;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BrandList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteConfirmed' => 'delete'];

    public $search = '';
    public $brandId;
    public $name;
    public $isEditMode = false;

    public function render()
    {
        $brands = Brand::where('tenant_id', Auth::user()->tenant_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.brand-list', [
            'brands' => $brands,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        $this->brandId = $id;
        $this->name = $brand->name;
        $this->isEditMode = true;
    }

    public function save()
    {
        // 1. Définition des règles
        $rules = [
            'name' => 'required|string|max:100|unique:brands,name,' . $this->brandId,
        ];

        // 2. Définition des messages d'erreur de validation traduits
        $messages = [
            'name.required' => __('brand.nom_requis'),
            'name.unique' => __('brand.nom_unique'),
            // 'name.max' pourrait aussi être ajouté ici si besoin d'un message spécifique
        ];

        // 3. Validation avec messages traduits
        $this->validate($rules, $messages);

        Brand::updateOrCreate(
            ['id' => $this->brandId],
            [
              'tenant_id' => Auth::user()->tenant_id,
              'name' => $this->name
            ]
        );

        // 4. Notification de succès traduite
        $messageKey = $this->isEditMode ? 'brand.marque_mise_a_jour' : 'brand.marque_creee';
        notyf()->success(__($messageKey));

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->brandId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        try {
            // Tentative de suppression
            Brand::find($this->brandId)->delete();
            // 5. Notification de suppression traduite
            notyf()->success(__('brand.marque_supprimee'));
        } catch (\Exception $e) {
            // Gestion d'erreur (ex: la marque est liée à un produit) avec message traduit
            notyf()->error(__('brand.erreur_marque'));
        }
    }

    private function resetInputFields()
    {
        $this->brandId = null;
        $this->name = '';
    }
}
