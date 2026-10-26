<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Validamos también los filtros para evitar búsquedas mal formadas.
        $filters = $request->validate([
            'search' => ['sometimes', 'string', 'max:150'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ]);

        // Cargamos la divisa en la misma consulta para evitar consultas extra.
        $query = Product::query()->with('currency')->orderBy('id');

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.trim($filters['search']).'%');
        }

        // El límite evita respuestas demasiado grandes si hay muchos productos.
        $perPage = (int) ($filters['per_page'] ?? 10);

        return ProductResource::collection($query->paginate($perPage));
    }

    public function store(StoreProductRequest $request)
    {
        // La solicitud ya llega validada por StoreProductRequest.
        $product = Product::create($request->validated());

        return (new ProductResource($product->load('currency')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Product $product): ProductResource
    {
        // Laravel devuelve 404 automáticamente si el producto no existe.
        return new ProductResource($product->load(['currency', 'prices.currency']));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return new ProductResource($product->refresh()->load(['currency', 'prices.currency']));
    }

    public function destroy(Product $product)
    {
        // Los precios relacionados se eliminan por la clave foránea CASCADE.
        $product->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}
