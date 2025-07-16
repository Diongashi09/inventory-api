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
        Schema::table('supplies', function (Blueprint $table) {
            // Define column names for clarity and robustness
            $originalSupplierId = 'supplier_id'; // The name of the column that was originally on 'supplies'
            $oldClientIdForSupplier = 'old_client_id_for_supplier'; // The new name for the original 'supplier_id' column
            $newSupplierId = 'supplier_id'; // The name for the NEW column that will link to 'vendor_companies'

            // No need to drop 'supplies_supplier_id_foreign' explicitly here,
            // as the previous error indicated it didn't exist when the migration first failed.
            // If it *did* exist and was successfully dropped, then the column was renamed.
            // If it *didn't* exist, then this line would fail anyway, so we omit it for this specific recovery.

            // CRITICAL FIX: Make renameColumn conditional
            // Only rename 'supplier_id' to 'old_client_id_for_supplier' if:
            // 1. The 'supplier_id' column still exists.
            // 2. The 'old_client_id_for_supplier' column does NOT already exist.
            if (Schema::hasColumn('supplies', $originalSupplierId) && !Schema::hasColumn('supplies', $oldClientIdForSupplier)) {
                $table->renameColumn($originalSupplierId, $oldClientIdForSupplier);
            }
            // If old_client_id_for_supplier already exists, it means the rename was successful
            // in a previous partial run, so we skip the rename operation.

            // CRITICAL FIX: Make addColumn conditional
            // Only add the new 'supplier_id' column if it does not already exist.
            if (!Schema::hasColumn('supplies', $newSupplierId)) {
                 $table->foreignId($newSupplierId)
                    ->nullable() // As per your requirement
                    ->after('supplier_type') // Position the new column
                    ->constrained('vendor_companies') // Link to the new table
                    ->nullOnDelete() // On vendor company delete, set this FK to null
                    ->name('supplies_vendor_company_fk'); // Explicitly name the FK to avoid conflicts
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $newSupplierId = 'supplier_id';
            $oldClientIdForSupplier = 'old_client_id_for_supplier';
            $originalSupplierId = 'supplier_id'; // Target name when fully rolled back

            // 1. Drop the new foreign key ('supplies_vendor_company_fk') first, if it exists.
            if (Schema::getConnection()->getDoctrineSchemaManager()->tablesExist('supplies')) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreignKeys = $sm->listTableForeignKeys('supplies');
                $fkExists = false;
                $fkName = 'supplies_vendor_company_fk';

                foreach ($foreignKeys as $fk) {
                    if ($fk->getName() === $fkName && $fk->getLocalColumns() == [$newSupplierId] && $fk->getForeignTableName() === 'vendor_companies') {
                        $fkExists = true;
                        break;
                    }
                }

                if ($fkExists) {
                    $table->dropForeign($fkName);
                }
            }

            // 2. Drop the newly added 'supplier_id' column, if it exists.
            if (Schema::hasColumn('supplies', $newSupplierId)) {
                $table->dropColumn($newSupplierId);
            }

            // 3. Rename 'old_client_id_for_supplier' back to 'supplier_id', if it exists.
            // This should only happen if 'old_client_id_for_supplier' exists AND
            // the 'supplier_id' (original name) doesn't exist (because it was renamed).
            if (Schema::hasColumn('supplies', $oldClientIdForSupplier) && !Schema::hasColumn('supplies', $originalSupplierId)) {
                $table->renameColumn($oldClientIdForSupplier, $originalSupplierId);
            }

            // 4. Re-add the original foreign key constraint to 'clients' table,
            // but only if the 'supplier_id' column exists and the FK doesn't already exist.
            if (Schema::hasColumn('supplies', $originalSupplierId)) {
                 $sm = Schema::getConnection()->getDoctrineSchemaManager();
                 $foreignKeys = $sm->listTableForeignKeys('supplies');
                 $originalFkExists = false;
                 $originalFkName = 'supplies_supplier_id_foreign'; // Laravel's expected default name
                 foreach ($foreignKeys as $fk) {
                     if ($fk->getName() === $originalFkName && $fk->getLocalColumns() == [$originalSupplierId] && $fk->getForeignTableName() === 'clients') {
                         $originalFkExists = true;
                         break;
                     }
                 }
                 if (!$originalFkExists) {
                     $table->foreignId($originalSupplierId)->nullable()->constrained('clients')->nullOnDelete();
                 }
            }
        });
    }
};
