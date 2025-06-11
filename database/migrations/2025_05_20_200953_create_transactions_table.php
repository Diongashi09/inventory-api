<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction_type',['supply','invoice']);
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price',10,2);
            $table->decimal('total_price',10,2);
            $table->enum('direction',['in','out']);
            $table->decimal('tariff_fee',10,2)->default(0);
            $table->decimal('import_cost',10,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
