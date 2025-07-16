<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\Transaction;
use App\Services\SupplieService;
use App\Http\Resources\SupplieResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\VendorCompany;

class SupplieController extends Controller
{
    protected SupplieService $service;

    public function __construct(SupplieService $service)
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(Supply::class, 'supply');//policy
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $query = Supply::with(['vendorCompany','creator','items.product']);

        // Add search by reference number OR supplier name
        if ($request->has('search') && !empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('reference_number', 'like', $searchTerm . '%')
                  ->orWhereHas('vendorCompany', function ($qVendor) use ($searchTerm) {
                      $qVendor->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        $query->orderBy('date','desc');

        return SupplieResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_type'    => 'required|in:person,company',
            'supplier_id'      => 'nullable|exists:vendor_companies,id',
            'tariff_fee'       => 'nullable|numeric',
            'import_cost'      => 'nullable|numeric',
            'status'           => 'in:pending,in_review,received,cancelled',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
        ]);

        $data['status'] = 'pending';

        $supply = $this->service->createSupplyRequest($data)->load(['vendorCompany', 'creator', 'items.product']);

        $savedProduct = \App\Models\Product::find($data['items'][0]['product_id']);
        \Log::info('Final stock after service:', ['stock_quantity' => $savedProduct->stock_quantity]);

        return response()->json(new SupplieResource($supply), 201);
    }

    public function show(Supply $supply)
    {
        $supply->load(['vendorCompany','creator','items.product']);
        return new SupplieResource($supply);
    }

    public function update(Request $request, Supply $supply)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,in_review,received,cancelled',
            // 'supplier_type' => 'sometimes|required|in:person,company',
            // 'supplier_id'   => 'sometimes|nullable|exists:vendor_companies,id',
            // 'tariff_fee'    => 'sometimes|nullable|numeric',
            // 'import_cost'   => 'sometimes|nullable|numeric',
            // 'items'              => 'sometimes|array|min:1', // If items can be updated here
            // 'items.*.product_id' => 'sometimes|required|exists:products,id',
            // 'items.*.quantity'   => 'sometimes|required|integer|min:1',
            // 'items.*.unit_price' => 'sometimes|required|numeric',
        ]);

        $oldStatus = $supply->status;


        // $supply->update($data);
        $supply->update(['status' => $request->input('status')]);

        if($oldStatus !== 'received' && $supply->status === 'received'){
            $this->service->receiveSupply($supply);
        }

        $supply->load(['vendorCompany', 'creator', 'items.product']);
        // return response()->json($supply);
        return response()->json(new SupplieResource($supply));
    }

    public function destroy(Supply $supply)
    {
        $supply->delete();
        return response()->json(null, 204);
    }
}

//public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'reference_number' => 'required|string|unique:supplies',
    //         'date'             => 'required|date',
    //         'supplier_type'    => 'required|in:person,company',
    //         'supplier_id'      => 'nullable|exists:clients,id',
    //         'tariff_fee'       => 'nullable|numeric',
    //         'import_cost'      => 'nullable|numeric',
    //         'status'           => 'in:pending,in_review,received,cancelled',
    //         'items'            => 'required|array|min:1',
    //         'items.*.product_id'   => 'required|exists:products,id',
    //         'items.*.quantity'     => 'required|integer|min:1',
    //         'items.*.unit_price'   => 'required|numeric',
    //     ]);

    //     DB::transaction(function() use ($data) {
    //         $supply = Supply::create([
    //             'reference_number' => $data['reference_number'],
    //             'date'             => $data['date'],
    //             'supplier_type'    => $data['supplier_type'],
    //             'supplier_id'      => $data['supplier_id'] ?? null,
    //             'tariff_fee'       => $data['tariff_fee'] ?? 0,
    //             'import_cost'      => $data['import_cost'] ?? 0,
    //             'status'           => $data['status'] ?? 'pending',
    //             'created_by'       => auth()->id(),
    //         ]);

    //         foreach ($data['items'] as $i) {
    //             $item = $supply->items()->create([
    //                 'product_id'  => $i['product_id'],
    //                 'quantity'    => $i['quantity'],
    //                 'unit_price'  => $i['unit_price'],
    //                 'total_price' => $i['quantity'] * $i['unit_price'],
    //             ]);

    //             $product = $item->product;
    //             $product->increment('stock_quantity', $item->quantity);

    //             Transaction::create([
    //                 'transaction_type' => 'supply',
    //                 'reference_id'     => $supply->id,
    //                 'product_id'       => $product->id,
    //                 'quantity'         => $item->quantity,
    //                 'unit_price'       => $item->unit_price,
    //                 'total_price'      => $item->total_price,
    //                 'direction'        => 'in',
    //                 'tariff_fee'       => $supply->tariff_fee,
    //                 'import_cost'      => $supply->import_cost,
    //                 'created_by' => auth()->id(),
    //             ]);
    //         }
    //     });

    //     return response()->json(['message' => 'Supply created'], 201);
    // }