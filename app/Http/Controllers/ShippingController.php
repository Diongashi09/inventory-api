<?php

namespace App\Http\Controllers;

use App\Models\Shipping;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Shipping::with(['invoice.client','invoice.creator']);

        if ($request->has('search') && !empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('tracking_id', 'like', '%' . $searchTerm . '%') // Search by tracking ID
                  ->orWhereHas('invoice', function ($invQuery) use ($searchTerm) {
                      $invQuery->where('reference_number', 'like', '%' . $searchTerm . '%'); // Search by invoice reference number
                  })
                  ->orWhereHas('invoice.client', function ($clientQuery) use ($searchTerm) {
                      $clientQuery->where('name', 'like', '%' . $searchTerm . '%'); // Search by client name
                  });
            });
        }

        $shippings = $query->get();

        return response()->json($shippings);
    }

    public function show(Shipping $shipping)
    {
        $shipping->load(['invoice.client','invoice.creator']);

        return response()->json($shipping);
    }
}
