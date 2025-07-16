<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Services\InvoiceService;
use App\Http\Resources\InvoiceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//When creating invoices or supplying products, allow selection of the warehouse in future logic.

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(Invoice::class, 'invoice');
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $user = $request->user()->loadMissing('client');

        $query = Invoice::with(['client','creator','items.product','shipping']);

        if($user->isClient()){
            if(!$user->client){
                return response()->json(['message' => 'Client profile not found'],404);
            }

            //Filter invoices for this specific client
            $query->where('customer_id',$user->client->id);
        }

        // Add search by reference number OR client name
        if ($request->has('search') && !empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('reference_number', 'like', $searchTerm . '%')
                  ->orWhereHas('client', function ($qClient) use ($searchTerm) {
                      $qClient->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        $query->orderBy('date','desc');

        // return Invoice::with(['client','creator','items.product'])->get();
        return InvoiceResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();


        //this way validate ska me u bo edhe pse eshte bo merge pershkak qe e use origin request
        // if($user->isClient() && $user->client){
        //     $request->merge([
        //         'customer_id' => $user->client->id,
        //     ]);
        // }

        if ($user->isClient() && $user->client) {
            // $request->replace(array_merge($request->all(), [
            //     'customer_id' => $user->client->id,
            // ]));
            $request->request->set('customer_id', $user->client->id);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:clients,id',
            'products'    => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'shipping_company' => 'sometimes|string|max:255',
            'shipping_cost' => 'sometimes|numeric|min:0',
        ]);
    
        $invoice = $this->invoiceService->createInvoice($validated);
    
        return response()->json([
            'message' => 'Invoice created successfully.',
            'invoice' => new InvoiceResource($invoice->load(['client', 'creator', 'items.product']))
            // 'invoice' => $invoice->load(['client', 'creator', 'items.product']),
        ], 201);
        
    }

    public function show(Invoice $invoice)
    {
        return new InvoiceResource($invoice->load(['client','creator','items.product']));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            // 'reference_number' => 'sometimes|required|string|unique:invoices,reference_number,' . $invoice->id,
            'date'             => 'sometimes|required|date',
            'customer_type'    => 'sometimes|required|in:person,company',
            'customer_id'      => 'nullable|exists:clients,id',
        ]);
    
        $invoice->update($data);
    
        return response()->json([
            'message' => 'Invoice updated successfully.',
            'invoice' => $invoice->fresh(['client', 'creator', 'items.product']),
        ]);//fresh() kthen new instance me fresh data dmth te qe jon bo update
        // refresh() e bon update instancen 
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json(null, 204);
    }

    public function startShipping(Request $request, Invoice $invoice)
    {
        // $trackingId = $request->input('tracking_id');
        
        // (new InvoiceService())->startShipping($invoice, $trackingId);
        
        // return response()->json(['message' => 'Shipping started.']);

        $shippingCompany = $request->input('shipping_company');
        $shippingCost = $request->input('shipping_cost'); // Capture from request if provided

        $this->invoiceService->startShipping($invoice, $shippingCompany, $shippingCost);

        return response()->json(['message' => 'Shipping started.']);
    }
}