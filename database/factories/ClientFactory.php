<?php

namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'            => $this->faker->company,
            'client_type'     => 'company',
            'contact_person'  => $this->faker->name,
            'phone'           => $this->faker->phoneNumber,
            'email'           => $this->faker->companyEmail,
            'address'         => $this->faker->address,
            'additional_info' => $this->faker->sentence,
        ];
    }
}
