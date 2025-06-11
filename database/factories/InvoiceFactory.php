<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'reference_number' => strtoupper($this->faker->unique()->bothify('INV-####')),
            'date' => $this->faker->date(),
            'customer_type' => $this->faker->randomElement(['person', 'company']),
            'customer_id' => Client::inRandomOrder()->first()?->id ?? Client::factory(),
            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
        ];
    }
}
