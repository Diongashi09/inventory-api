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

    public function index()
    {
        $shippings = Shipping::with(['invoice.client','invoice.creator'])->get();

        return response()->json($shippings);
    }

    public function show(Shipping $shipping)
    {
        $shipping->load(['invoice.client','invoice.creator']);

        return response()->json($shipping);
    }
}
