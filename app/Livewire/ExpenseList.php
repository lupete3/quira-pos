<?php

namespace App\Livewire;

use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public $operationType;
    public $storeIdUser;

    protected $listeners = ['deleteConfirmed' => 'delete', 'validateConfirmed' => 'validateExpense'];

    public function mount()
    {
      if (Auth::user()->role_id == 1) {
        $this->store_id = null;
      } else {
          $store = Auth::user()->stores()->first();
        $this->storeIdUser = $store->id;
      }
    }

    protected function rules()
    {
        return [
            'category_id' => 'required|exists:expense_categories,id',
            'store_id'    => Auth::user()->role_id == 1 ? 'required|exists:stores,id' : 'nullable',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'date'        => 'required|date',
        ];
    }

    public function render()
    {
        if (Auth::user()->role_id != 1) {
            $store = Auth::user()->stores()->first();
            $this->store_id = $store?->id; // sécurisation si pas de magasin
        }

        $expenses = Expense::with(['category', 'store'])
            ->where('tenant_id', Auth::user()->tenant_id)
            ->when($this->store_id, fn($q) => $q->where('store_id', $this->store_id))
            ->where(function ($query) {
                $query->whereHas('category', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('store', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate($this->perPage);

        $stores = Store::all();

        return view('livewire.expense-list', [
            'expenses'   => $expenses,
            'categories' => ExpenseCategory::where('tenant_id', Auth::user()->tenant_id)->get(),
            'stores'     => $stores,
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

        // Si ce n'est pas un admin, on prend automatiquement le store de l'utilisateur
        if (Auth::user()->role_id != 1) {
            $store = Auth::user()->stores()->first();
            $this->store_id = $store?->id; // sécurisation si pas de magasin
            $cashRegister = $store->cashRegister;
        }

        $store = Store::find($this->store_id);
        $cashRegister = $store->cashRegister;

        if ($this->amount > $cashRegister->current_balance) {
          notyf()->error(__('Le montant dépensé est supérieur au solde de la caisse.'));
          return;
        }

        Expense::updateOrCreate(
            ['id' => $this->expenseId],
            [
                'tenant_id' => Auth::user()->tenant_id,
                'expense_category_id' => $this->category_id,
                'store_id'    => $this->store_id,
                'user_id'     => Auth::id(),
                'amount'      => $this->amount,
                'description' => $this->description,
                'expense_date'=> $this->date,
            ]
        );

        notyf()->success(__($this->isEditMode ? 'Dépense mise à jour.' : 'Dépense enregistrée.'));

        $this->dispatch('close-modal');
        $this->resetInputFields();
    }

    public function confirmValidate($id, string $type)
    {
        $this->operationType = $type;
        $this->expenseId = $id;
        $this->dispatch('show-validate-confirmation');
    }

    public function validateExpense()
    {
        if ($this->operationType == 'cancelled') {
          $expense = Expense::findOrFail($this->expenseId)->update([
            'status' => $this->operationType
          ]);
          notyf()->warning(__('La dépense a été annulée.'));
          $this->dispatch('close-validate-confirmation');
          return;
        }

        if ($this->operationType == 'validated') {
          DB::transaction(function () {
            try {

              $expense = Expense::findOrFail($this->expenseId);
              // Enregistrer l'entrée en caisse
              $store = Store::find($expense->store_id);

              $cashRegister = $store->cashRegister;

              if ($expense->amount > 0 && $cashRegister && $cashRegister->current_balance >= $expense->amount) {
                  // Création de la transaction OUT
                  CashTransaction::create([
                      'tenant_id' => $expense->tenant_id,
                      'cash_register_id' => $cashRegister->id,
                      'type' => 'out',
                      'amount' => $expense->amount,
                      'description' => $expense->description. ' Dépense #' . $expense->id,
                      'user_id' => Auth::id(),
                  ]);

                  // Mise à jour du solde de la caisse
                  $cashRegister->decrement('current_balance', $expense->amount);
                  $expense->update(['status' => $this->operationType]);
                  $expense->save();
                  $this->dispatch('close-validate-confirmation');
                  notyf()->success(__('La dépense a été validée.'));

              }else{
                DB::rollBack();
                throw new \Exception("Le montant dépensé est supérieur au solde de la caisse.");
                notyf()->error(__('Le montant dépensé est supérieur au solde de la caisse.'));
              }

            } catch (\Throwable $th) {
              DB::rollBack();
              notyf()->error(__('Une erreur s\'est produite lors de la validation de la dépense.'));
            }
          });

          DB::commit();
          $this->dispatch('close-validate-confirmation');
        }
    }

    public function confirmDelete($id)
    {
        $this->expenseId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        Expense::findOrFail($this->expenseId)->delete();
        notyf()->success(__('Dépense supprimée.'));
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
