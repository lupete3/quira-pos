<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class CategoryList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';


    public $search = '';
    public $categoryId;
    public $name;
    public $description;
    public $isEditMode = false;

    protected $listeners = ['deleteConfirmed' => 'delete'];

    protected $rules = [
        'name' => 'required|string|max:100|unique:categories,name',
        'description' => 'nullable|string',
    ];

    public function render()
    {
        $categories = Category::where('tenant_id', Auth::user()->tenant_id)
          ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.category-list', [
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
        $category = Category::findOrFail($id);
        $this->categoryId = $id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->isEditMode = true;
    }

    public function save()
    {
        $tenantId = Auth::user()->tenant_id;
        $rules = [
            'name' => 'required|string|max:100|unique:categories,name,' . $this->categoryId . ',id,tenant_id,' . $tenantId,
            'description' => 'nullable|string',
        ];
        
        $this->validate($rules);

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            [
                'tenant_id' => Auth::user()->tenant_id,
                'name' => $this->name,
                'description' => $this->description,
            ]
        );

        notyf()->success(__($this->isEditMode ? 'Catégorie mise à jour avec succès.' : 'Catégorie créée avec succès.'));

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->categoryId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        Category::find($this->categoryId)->delete();
        notyf()->success(__('Catégorie supprimée avec succès.'));
        $this->dispatch('close-delete-confirmation');
    }

    private function resetInputFields()
    {
        $this->categoryId = null;
        $this->name = '';
        $this->description = '';
    }
}