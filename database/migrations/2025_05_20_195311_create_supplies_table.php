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
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->date('date');
            $table->enum('supplier_type',['person','company']);
            $table->foreignId('supplier_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->decimal('tariff_fee',10,2)->default(0);//2 digits after decimal point
            $table->decimal('import_cost',10,2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status',['pending','in_review','received','cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
