<?php

// use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SupplieController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\ShippingWebhookController;

Route::post('register', [AuthController::class,'register']);
Route::post('login',    [AuthController::class,'login']);
Route::post('forgot-password', [AuthController::class,'forgotPassword']);
Route::post('reset-password',  [AuthController::class,'resetPassword']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('logout', [AuthController::class,'logout']);
    Route::post('users/create-manager',[UserController::class,'createManager']);

    Route::apiResource('users',    UserController::class)->except(['store']);
    Route::apiResource('roles',    RoleController::class);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products',   ProductController::class);
    Route::apiResource('clients',    ClientController::class);

    Route::apiResource('supplies', SupplieController::class);
    Route::apiResource('invoices', InvoiceController::class);

    Route::get('transactions', [TransactionController::class,'index']);
    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);

    Route::post('/warehouse/transfer-all', [WarehouseTransferController::class, 'moveAllToMainWarehouse']);
    Route::post('/warehouses/refill-stock', [WarehouseTransferController::class, 'refillProductStockFromWarehouse']);

    Route::patch('/webhook/shipping-status', [ShippingWebhookController::class, 'update']);

    Route::post('/invoices/{invoice}/ship', [InvoiceController::class, 'startShipping']);
});