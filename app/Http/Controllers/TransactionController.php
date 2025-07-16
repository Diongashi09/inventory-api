<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionResource;


class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        // return Transaction::with('product')->get();
        $query = Transaction::with(['product', 'creator']);

        // Add search functionality for creator's name
        if ($request->has('search') && !empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $query->whereHas('creator', function ($creatorQuery) use ($searchTerm) {
                $creatorQuery->where('name', 'like', '%' . $searchTerm . '%');
            });
        }

        $query->orderBy('created_at','desc');
        return TransactionResource::collection($query->get());
    }

    public function show(Transaction $transaction)
    {
        // return $transaction->load('product','creator');
        $transaction->load(['product', 'creator']);
        return new TransactionResource($transaction);
    }
}