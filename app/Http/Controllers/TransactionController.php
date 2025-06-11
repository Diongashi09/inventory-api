<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        return Transaction::with('product')->get();
    }

    public function show(Transaction $transaction)
    {
        return $transaction->load('product','creator');
    }
}
