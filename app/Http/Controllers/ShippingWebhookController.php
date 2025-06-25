<?php

namespace App\Http\Controllers;

use App\Models\Shipping;
use Illuminate\Http\Request;

class ShippingWebhookController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'tracking_id' => 'required|string',
            'status' => 'required|in:on_delivery,delivered',
        ]);

        $shipping = Shipping::where('tracking_id', $request->tracking_id)->first();

        if (!$shipping) {
            return response()->json(['message' => 'Shipping not found'], 404);
        }

        $shipping->update(['status' => $request->status]);
        $shipping->invoice()->update(['status' => $request->status]);

        return response()->json(['message' => 'Shipping status updated successfully']);
    }
}