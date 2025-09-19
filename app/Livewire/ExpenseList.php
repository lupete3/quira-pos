<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public $expenseId;
    public $category_id;
    public $store_id;
    public $amount;
    public $description;
    public $date;
    public $isEditMode = false;

    protected $listeners = ['deleteConfirmed' => 'delete'];

    protected function rules()
    {
        return [
            'category_id' => 'required|exists:expense_categories,id',
            'store_id'    => 'required|exists:stores,id',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'date'        => 'required|date',
        ];
    }

    public function render()
    {
        $expenses = Expense::with(['category', 'store'])
            ->whereHas('category', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orWhereHas('store', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.expense-list', [
            'expenses'   => $expenses,
            'categories' => ExpenseCategory::all(),
            'stores'     => Store::all(),
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $this->expenseId = $id;
        $this->category_id = $expense->expense_category_id;
        $this->store_id = $expense->store_id;
        $this->amount = $expense->amount;
        $this->description = $expense->description;
        $this->date = $expense->expense_date->format('Y-m-d');
        $this->isEditMode = true;
    }

    public function save()
    {
        $this->validate();

        Expense::updateOrCreate(
            ['id' => $this->expenseId],
            [
                'expense_category_id' => $this->category_id,
                'store_id'    => $this->store_id,
                'user_id'     => Auth::user()->id,
                'amount'      => $this->amount,
                'description' => $this->description,
                'expense_date' => $this->date,
            ]
        );

        notyf()->success($this->isEditMode ? 'Dépense mise à jour.' : 'Dépense enregistrée.');

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->expenseId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        Expense::findOrFail($this->expenseId)->delete();
        notyf()->success('Dépense supprimée.');
        $this->dispatch('close-delete-confirmation');
    }

    private function resetInputFields()
    {
        $this->expenseId = null;
        $this->category_id = '';
        $this->store_id = '';
        $this->amount = '';
        $this->description = '';
        $this->date = now()->format('Y-m-d');
    }
}
