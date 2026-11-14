<?php

use App\Livewire\PurchaseForm;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('purchase product search starts after three characters', function () {
    $now = now();
    $tenantId = DB::table('tenants')->insertGetId([
        'name' => 'Test tenant',
        'email' => 'purchase-search-tenant@example.test',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $categoryId = DB::table('categories')->insertGetId([
        'tenant_id' => $tenantId,
        'name' => 'Test category',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $brandId = DB::table('brands')->insertGetId([
        'tenant_id' => $tenantId,
        'name' => 'Test brand',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $unitId = DB::table('units')->insertGetId([
        'tenant_id' => $tenantId,
        'name' => 'Each',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $user = User::factory()->create(['tenant_id' => $tenantId]);
    $this->actingAs($user);

    DB::table('products')->insert([
        'tenant_id' => $tenantId,
        'reference' => 'COF-001',
        'name' => 'Coffee beans',
        'category_id' => $categoryId,
        'brand_id' => $brandId,
        'unit_id' => $unitId,
        'purchase_price' => 5,
        'sale_price' => 8,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    Livewire::test(PurchaseForm::class)
        ->set('search', 'Co')
        ->assertDontSee('Coffee beans')
        ->set('search', 'Cof')
        ->assertSee('Coffee beans');
});
