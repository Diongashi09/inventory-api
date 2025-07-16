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
        Schema::table('shippings', function (Blueprint $table) {
            // drop existing FK
            $table->dropForeign(['invoice_id']);

            // make invoice_id nullable
            $table->unsignedBigInteger('invoice_id')->nullable()->change();

            // re-add FK
            $table
                ->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            Schema::table('shippings', function (Blueprint $table) {
                $table->dropForeign(['invoice_id']);
                $table->unsignedBigInteger('invoice_id')->nullable(false)->change();
                $table
                    ->foreign('invoice_id')
                    ->references('id')
                    ->on('invoices')
                    ->cascadeOnDelete();
            });
        });
    }
};
