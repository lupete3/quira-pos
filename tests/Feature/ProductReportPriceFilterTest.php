<?php

use App\Livewire\Reports\ProductReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('product report price bounds filter the product sale price', function () {
    $now = now();
    $tenantId = DB::table('tenants')->insertGetId([
        'name' => 'Test tenant',
        'email' => 'price-filter-tenant@example.test',
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

    foreach ([8, 15, 30] as $index => $salePrice) {
        DB::table('products')->insert([
            'tenant_id' => $tenantId,
            'reference' => 'PRICE-' . $index,
            'name' => 'Product ' . $index,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'unit_id' => $unitId,
            'purchase_price' => 5,
            'sale_price' => $salePrice,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $report = new ProductReport();
    $report->min_price = 10;
    $report->max_price = 20;

    expect($report->getFilteredProducts(false)->pluck('name')->all())
        ->toBe(['Product 1']);
});
