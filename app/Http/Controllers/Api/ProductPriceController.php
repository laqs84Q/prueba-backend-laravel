<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPriceRequest;
use App\Http\Resources\ProductPriceResource;
use App\Models\Product;
use Illuminate\Http\Response;

class ProductPriceController extends Controller
{
    public function index(Product $product)
    {
        // Solo mostramos los precios adicionales del producto solicitado.
        return ProductPriceResource::collection(
            $product->prices()->with('currency')->orderBy('id')->get()
        );
    }

    public function store(StoreProductPriceRequest $request, Product $product)
    {
        $price = $product->prices()->create($request->validated());

        return (new ProductPriceResource($price->load('currency')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
