<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RawMaterial;
use App\Models\RawMaterialTransaction;
use App\Models\Store;
use App\Models\StoreRawMaterial; // Correct pivot usage
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RawMaterialStockEntry extends Component
{
    public $materialId;
    public $rawMaterial;
    public $store_id;
    public $quantity;
    public $type = 'entry'; // 'entry' or 'exit'
    public $description;
    public $stores = [];

    protected $listeners = ['openStockEntryModal'];

    private function getUserStores($tenantId)
    {
        $user = Auth::user();

        if ($user->role_id == 1) {
            return Store::where('tenant_id', $tenantId)->get();
        }

        return $user->stores()->where('tenant_id', $tenantId)->get();
    }

    public function mount()
    {
        $this->stores = $this->getUserStores(Auth::user()->tenant_id);

        // Default store if only one exists
        if ($this->stores->count() == 1) {
            $this->store_id = $this->stores->first()->id;
        }
    }

    public function openStockEntryModal($materialId)
    {
        $this->reset(['quantity', 'description', 'store_id', 'type']);
        $this->materialId = $materialId;
        $this->rawMaterial = RawMaterial::find($materialId);

        // Reload stores to be safe
        $this->mount();

        $this->dispatch('open-stock-modal');
    }

    public function save()
    {
        $this->validate([
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|numeric|min:0.01',
            'type' => 'required|in:entry,exit',
            'description' => 'nullable|string|max:255',
        ]);

        $tenantId = Auth::user()->tenant_id;
        // Verify store access
        $userStores = $this->getUserStores($tenantId);
        if (!$userStores->contains('id', $this->store_id)) {
            notyf()->error(__('raw_material.access_denied_store'));
            return;
        }

        DB::transaction(function () use ($tenantId) {
            // Create Transaction
            RawMaterialTransaction::create([
                'tenant_id' => $tenantId,
                'store_id' => $this->store_id,
                'raw_material_id' => $this->materialId,
                'type' => $this->type,
                'quantity' => $this->quantity,
                'description' => $this->description,
                'user_id' => Auth::id(),
            ]);

            // Update Stock
            // We use the pivot table store_raw_materials
            // We can check if it exists or use updateOrInsert logic manually or via relationship?
            // Safer to be explicit.

            $pivot = DB::table('store_raw_materials')
                ->where('store_id', $this->store_id)
                ->where('raw_material_id', $this->materialId)
                ->first();

            $currentQty = $pivot ? $pivot->quantity : 0;

            if ($this->type === 'entry') {
                $newQty = $currentQty + $this->quantity;
            } else {
                $newQty = $currentQty - $this->quantity;
                // Allow negative stock for corrections or POS flexibility
            }

            if ($pivot) {
                DB::table('store_raw_materials')
                    ->where('id', $pivot->id)
                    ->update(['quantity' => $newQty, 'updated_at' => now()]);
            } else {
                DB::table('store_raw_materials')->insert([
                    'store_id' => $this->store_id,
                    'raw_material_id' => $this->materialId,
                    'quantity' => $newQty,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        });

        notyf()->success(__('raw_material.stock_movement_success'));
        $this->dispatch('close-stock-modal'); // Close modal JS event
        $this->dispatch('refreshList'); // Update parent list
    }

    public function render()
    {
        return view('livewire.raw-material-stock-entry');
    }
}
