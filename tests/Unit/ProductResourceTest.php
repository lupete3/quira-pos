<?php

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

uses(Tests\TestCase::class);

test('cashiers do not receive product purchase prices', function () {
    $request = Request::create('/');
    $request->setUserResolver(fn () => new User(['role_id' => 3]));
    $product = new Product(['purchase_price' => 12.50, 'sale_price' => 18.00]);

    $data = (new ProductResource($product))->resolve($request);

    expect($data)->not->toHaveKey('purchase_price');
});

test('other roles continue to receive product purchase prices', function () {
    $request = Request::create('/');
    $request->setUserResolver(fn () => new User(['role_id' => 1]));
    $product = new Product(['purchase_price' => 12.50, 'sale_price' => 18.00]);

    $data = (new ProductResource($product))->resolve($request);

    expect($data)->toHaveKey('purchase_price', 12.50);
});
