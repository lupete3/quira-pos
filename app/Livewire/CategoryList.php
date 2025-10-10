<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

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
        $rules = [
            'name' => 'required|string|max:100|unique:categories,name,' . $this->categoryId . ',id,tenant_id,' . Auth::user()->tenant_id,
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

        notyf()->success(
            __($this->isEditMode ? 'category.categorie_mise_a_jour' : 'category.categorie_creee')
        );

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
        Category::findOrFail($this->categoryId)->delete();

        notyf()->success(__('category.categorie_supprimee'));
        $this->dispatch('close-delete-confirmation');
    }

    private function resetInputFields()
    {
        $this->categoryId = null;
        $this->name = '';
        $this->description = '';
    }
}
