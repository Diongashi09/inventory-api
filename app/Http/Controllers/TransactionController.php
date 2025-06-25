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
        $transactions = Transaction::with(['product', 'creator'])->get();
        return TransactionResource::collection($transactions);
    }

    public function show(Transaction $transaction)
    {
        // return $transaction->load('product','creator');
        $transaction->load(['product', 'creator']);
        return new TransactionResource($transaction);
    }
}