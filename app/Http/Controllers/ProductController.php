<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Warehouse;

use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request)
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


        //////

        // return Product::with('category')->get();

        $query = Product::with('category');

        // Add search by product name or description
        if ($request->has('search') && !empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        //filtering by category
        if($request->has('category_id')){
            $categoryId = $request->input('category_id');

            //Validate 
            if(!Category::where('id',$categoryId)->exists()){
                return response()->json(['message' => 'Invalid category ID provided'],400);
            }
            $query->where('category_id',$categoryId);
        }

        //Sorting by price
        if($request->has('sort_by')){
            $sortBy = $request->input('sort_by');
            if($sortBy === 'price_asc'){
                $query->orderBy('price_excl_vat','asc');
            } elseif($sortBy === 'price_desc'){
                $query->orderBy('price_excl_vat','desc');
            }
        } else {
            // $query->orderBy('name','asc');
            $query->orderBy('id','asc');
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string',
            'description'    => 'nullable|string',
            'category_id'    => 'nullable|exists:categories,id',
            'stock_quantity' => 'numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'price_excl_vat' => 'required|numeric',
            'vat_rate'       => 'required|numeric',
            'unit'           => 'required|in:pcs,kg,ltr',
        ]);


        return DB::transaction(function () use ($data) {
            $product = Product::create($data);
    
            // --- NEW LOGIC FOR WAREHOUSE ASSOCIATION ---
            $warehouse = Warehouse::first(); // Or a specific warehouse ID from request
    
            $warehouse->products()->attach($product->id, [
                'quantity' => $data['stock_quantity'],
            ]);

            Log::info("Created product #{$product->id} - set stock_quantity={$data['stock_quantity']} and pivot entry. ");

            return response()->json([
                'message' => 'Product created successfully.',
                'product' => $product->load('warehouses'),
            ], 201);

            // if ($mainWarehouse) {
            //     // Attach the new product to the main warehouse with an initial quantity (e.g., 0)
            //     // You might want to allow this initial quantity to be specified in the request
            //     $initialWarehouseQuantity = $request->input('initial_stock_quantity', 0); // Allow initial stock
            //     $mainWarehouse->products()->attach($product->id, ['quantity' => $initialWarehouseQuantity]);
    
            //     // Ensure products.stock_quantity is also updated
            //     $product->update(['stock_quantity' => $initialWarehouseQuantity]);
    
            //     // Log this for clarity
            //     Log::info("Product created: {$product->name}. Added to warehouse {$mainWarehouse->name} with initial quantity: {$initialWarehouseQuantity}.");
    
            // } else {
            //     Log::warning("No main warehouse found for new product {$product->name}. Product created without warehouse stock entry.");
            // }
            // --- END NEW LOGIC ---
    
            // return response()->json([
            //     'message' => 'Product created successfully.',
            //     'product' => $product
            // ], 201);
        });

        // return response()->json(Product::create($data), 201);
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
            'low_stock_threshold' => 'required|integer|min:0',
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