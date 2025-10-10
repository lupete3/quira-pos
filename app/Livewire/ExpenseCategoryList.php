<?php

namespace App\Livewire;

use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule; // Ajout pour la validation unique

class ExpenseCategoryList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $categoryId;
    public $name;
    public $description;
    public $isEditMode = false;

    protected $listeners = ['deleteConfirmed' => 'delete'];

    protected function rules()
    {
        // Utilisation de Rule::unique pour exclure l'ID actuel lors de la mise à jour
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('expense_categories', 'name')
                    ->ignore($this->categoryId)
                    ->where(fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id)),
            ],
            'description' => 'nullable|string',
        ];
    }

    protected function validationAttributes()
    {
        // Traduction des noms d'attributs (Nom)
        return [
            'name' => __('expense_category.nom'),
        ];
    }

    protected function messages()
    {
        // Traduction des messages d'erreur spécifiques
        return [
            // Clé : nom_requis
            'name.required' => __('expense_category.nom_requis'),
            // Clé : nom_unique
            'name.unique' => __('expense_category.nom_unique'),
        ];
    }

    public function render()
    {
        $categories = ExpenseCategory::where('tenant_id', Auth::user()->tenant_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.expense-category-list', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $this->categoryId = $id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->isEditMode = true;
    }

    public function save()
    {
        try {
            $this->validate();

            ExpenseCategory::updateOrCreate(
                ['id' => $this->categoryId],
                [
                    'tenant_id' => Auth::user()->tenant_id,
                    'name' => $this->name,
                    'description' => $this->description,
                ]
            );
            
            // Clés : categorie_mise_a_jour / categorie_creee
            $message = $this->isEditMode 
                ? __('expense_category.categorie_mise_a_jour') 
                : __('expense_category.categorie_creee');

            notyf()->success($message);

            $this->dispatch('close-modal');
            $this->resetInputFields();

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Laissez la validation afficher les messages d'erreur dans le formulaire
            throw $e;
        } catch (\Exception $e) {
            // Clé : erreur
            notyf()->error(__('expense_category.erreur') . ' : ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->categoryId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        try {
            ExpenseCategory::findOrFail($this->categoryId)->delete();
            // Clé : categorie_supprimee
            notyf()->success(__('expense_category.categorie_supprimee'));
        } catch (\Exception $e) {
            // Clé : erreur
            notyf()->error(__('expense_category.erreur') . ' : ' . $e->getMessage());
        } finally {
            $this->dispatch('close-delete-confirmation');
            $this->resetInputFields();
        }
    }

    private function resetInputFields()
    {
        $this->categoryId = null;
        $this->name = '';
        $this->description = '';
    }
}