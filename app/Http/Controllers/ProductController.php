<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(Product::class, 'product');
    }

    public function index()
    {
        // try {
        //     return Product::with('category')->get();
        // } catch (\Throwable $e) {
        //     // logs the full exception to storage/logs/laravel.log
        //     Log::error('Products@index error: '.$e->getMessage(), [
        //         'trace'=>$e->getTraceAsString()
        //     ]);
        //     // return a safe JSON response
        //     return response()->json([
        //         'error'   => 'Server error fetching products',
        //         'details' => $e->getMessage(),    // you can remove in prod
        //     ], 500);
        // }

        return Product::with('category')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string',
            'description'    => 'nullable|string',
            'category_id'    => 'nullable|exists:categories,id',
            'stock_quantity' => 'numeric|min:0',
            'price_excl_vat' => 'required|numeric',
            'vat_rate'       => 'required|numeric',
            'unit'           => 'required|in:pcs,kg,ltr',
        ]);

        return response()->json(Product::create($data), 201);
    }

    public function show(Product $product)
    {
        return $product->load('category');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'           => 'sometimes|required|string',
            'description'    => 'nullable|string',
            'category_id'    => 'nullable|exists:categories,id',
            'stock_quantity' => 'numeric|min:0',
            'price_excl_vat' => 'numeric',
            'vat_rate'       => 'numeric',
            'unit'           => 'sometimes|in:pcs,kg,ltr',
        ]);

        $product->update($data);
        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}