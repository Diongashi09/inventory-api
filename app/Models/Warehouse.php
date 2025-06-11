<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Warehouse extends Model
{
    protected $fillable = ['name', 'location'];

    public function products()
    {
        return $this->belongsToMany(Product::class,'warehouse_product')->withPivot('quantity')->withTimestamps();
    }//with pivot table tells laravel tells Laravel:When I access products for a warehouse, I also want to access the quantity from the pivot table (warehouse_product).
}

//the only models B that will be assigned to the model A are the ones in a received group —sync()
//only the missing models B from the received group will be assigned to model A — syncWithoutDetaching()
//We used the syncWithoutDetaching() method for any case we wanted to add a new models to the relationship but only the missing ones. The perfect case for this helper method

// PS C:\Users\Admin\Desktop\inventory-api> php artisan tinker
// Psy Shell v0.12.8 (PHP 8.3.21 — cli) by Justin Hileman
// > $warehouse = App\Models\Warehouse::first();
// = App\Models\Warehouse {#6233
//     id: 1,
//     name: "Main Warehouse",
//     location: "Default Location",
//     created_at: "2025-05-31 17:45:39",
//     updated_at: "2025-05-31 17:45:39",
//   }

// > $product = App\Models\Product::find(1);
// = App\Models\Product {#6215
//     id: 1,
//     name: "est",
//     description: "Sint iure sapiente commodi nisi odio velit similique.",
//     category_id: 3,
//     stock_quantity: 26,
//     price_excl_vat: "38.56",
//     vat_rate: "20.00",
//     unit: "ltr",
//     created_at: "2025-05-23 22:44:07",
//     updated_at: "2025-05-31 16:53:48",
//     price_incl_vat: "46.27",
//   }

// > $warehouse->products()->syncWithoutDetaching([$product->id => ['quantity' => 50]]);   
// = [
//     "attached" => [
//       1,
//     ],
//     "detached" => [],
//     "updated" => [],
//   ]

//"attached" => [1] → Product with ID 1 was successfully linked to the warehouse with quantity 50.

// "detached" => [] → No existing links were removed.

// "updated" => [] → No existing pivot rows were updated (it was a new attach, not an update).
////
//secondTime

//PS C:\Users\Admin\Desktop\inventory-api> php artisan tinker
// Psy Shell v0.12.8 (PHP 8.3.21 — cli) by Justin Hileman
// > $warehouse = App\Models\Warehouse::first();
// = App\Models\Warehouse {#6233
//     id: 1,
//     name: "Main Warehouse",
//     location: "Default Location",
//     created_at: "2025-05-31 17:45:39",
//     updated_at: "2025-05-31 17:45:39",
//   }

// > $product = App\Models\Product::find(1);
// = App\Models\Product {#6215
//     id: 1,
//     name: "est",
//     description: "Sint iure sapiente commodi nisi odio velit similique.",
//     category_id: 3,
//     stock_quantity: 26,
//     price_excl_vat: "38.56",
//     vat_rate: "20.00",
//     unit: "ltr",
//     created_at: "2025-05-23 22:44:07",
//     updated_at: "2025-05-31 16:53:48",
//     price_incl_vat: "46.27",
//   }

// > $warehouse->products()->syncWithoutDetaching([$product->id => ['quantity' => 50]]);   
// = [
//     "attached" => [],
//     "detached" => [],
//     "updated" => [
//       1,
//     ],
//   ]

// > $currentQty = $warehouse->products()->find($product->id)->pivot->quantity;
// = 50

// > $warehouse->products()->updateExistingPivot($product->id,['quantity'=>$currentQty+10]);
// = 1

// >