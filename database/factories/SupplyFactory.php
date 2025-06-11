<?php

namespace Database\Factories;
use App\Models\Supply;
use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supply>
 */
class SupplyFactory extends Factory
{
    protected $model = Supply::class;


    public function definition(): array
    {
        return [
            'reference_number' => strtoupper($this->faker->unique()->bothify('SUP-####')),
            'date' => $this->faker->date(),
            'supplier_type' => $this->faker->randomElement(['person', 'company']),
            'supplier_id' => Client::inRandomOrder()->first()?->id ?? Client::factory(),
            'tariff_fee' => $this->faker->randomFloat(2, 10, 1000),
            'import_cost' => $this->faker->randomFloat(2, 100, 5000),
            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'status' => $this->faker->randomElement(['pending', 'in_review', 'received', 'cancelled']),
        ];
    }
}
