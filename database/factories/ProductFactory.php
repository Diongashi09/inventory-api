<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'           => $this->faker->word,
            'description'    => $this->faker->sentence,
            'category_id'    => Category::inRandomOrder()->first()?->id,
            'stock_quantity' => $this->faker->numberBetween(0,100),
            'price_excl_vat' => $this->faker->randomFloat(2,1,100),
            'vat_rate'       => 20,
            'unit'           => $this->faker->randomElement(['pcs','kg','ltr']),
        ];
    }
}
