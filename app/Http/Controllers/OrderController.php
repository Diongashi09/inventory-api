<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $svc;

    public function __construct(OrderService $svc)
    {
        $this->middleware('auth:sanctum');
        // $this->authorizeResource(Order::class,'order');
        $this->svc = $svc;
    }

    public function index(Request $req)
    {
        $q = Order::with(['client','creator','items.product']);
        if ($req->filled('search')) {
            $s = $req->input('search');
            $q->where('reference_number','like',"$s%")
              ->orWhereHas('client',fn($q2)=>$q2->where('name','like',"%$s%"));
        }
        return OrderResource::collection($q->orderBy('date','desc')->get());
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            // 'customer_type'     => 'required|in:person,company',
            'customer_id'       => 'required|exists:clients,id',
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.quantity'  => 'required|integer|min:1',
        ]);

        $order = $this->svc->placeOrder([
            // 'customer_type'   => $data['customer_type'],
            'customer_id'     => $data['customer_id'],
            // your service expects `products`, not `items`:
            'products'        => array_map(fn($i)=>[
                    'id'       => $i['product_id'],
                    'quantity' => $i['quantity'],
                    ], $data['items']),
        // if you ever want to pass shipping overrides:
            'shipping_company'=> $data['shipping_company'] ?? null,
            'shipping_cost'   => $data['shipping_cost'] ?? null,
        ])->load(['client','creator','items.product']);

        if ($order->status==='awaiting_stock') {
            return response()->json([
              'message' => 'Out of stock: a supply request has been placed and we’ll ship as soon as it arrives.',
              'order'   => new OrderResource($order),
            ], 201);
        }

        return response()->json(new OrderResource($order), 201);
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load(['client','creator','items.product']));
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->noContent();
    }
}