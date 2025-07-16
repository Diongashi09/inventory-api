<?php

namespace App\Http\Controllers;

use App\Models\Shipping;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class ShippingWebhookController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->middleware('auth:sanctum');
        $this->invoiceService = $invoiceService;
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'tracking_id' => 'required|string',
            'status' => 'required|in:on_delivery,delivered',
        ]);

        $shipping = Shipping::where('tracking_id', $data['tracking_id'])->with('order.items')->firstOrFail();

        if (!$shipping) {
            return response()->json(['message' => 'Shipping not found'], 404);
        }

        // $shipping->update(['status' => $request->status]);
        // $shipping->invoice()->update(['status' => $request->status]);

        $shipping->update(['status' => $data['status']]);

        // 2) propagate into the “invoice” (in your case your Order record)
        // $order = $shipping->invoice; 
        // $order->update(['status' => $data['status']]);

        // 3) if we’ve just delivered → bill the customer
        if($shipping->status === 'delivered'){
            $order = $shipping->order;

            $payload = [
                'customer_type'    => $order->customer_type,              // ← add this
                'customer_id'      => $order->customer_id,
                'products'         => $order->items->map(function($i){
                    return ['id' => $i->product_id, 'quantity' => $i->quantity];
                })->toArray(),
                'shipping_company' => $shipping->shipping_company,
                'shipping_cost'    => $shipping->shipping_cost,
            ];

            $invoice = $this->invoiceService->createInvoice($payload);

            $shipping->invoice_id = $invoice->id;
            $shipping->save();
        }

        return response()->json(['message' => 'Shipping status updated successfully']);
    }
}