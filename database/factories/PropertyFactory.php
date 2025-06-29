<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
       return [
            'owner_id' => fake()->numberBetween(1, 10),
            'owner_type' => fake()->randomElement(['App\Models\User', 'App\Models\Office']),
            'ad_number' => strtoupper(Str::random(10)),
            'title' => fake()->sentence,
            'description' => fake()->paragraph,
            'price' => fake()->randomFloat(2, 10000, 1000000),
            'location' => fake()->address,
            'latitude' => fake()->latitude,
            'longitude' => fake()->longitude,
            'area' => fake()->randomFloat(2, 50, 500),
            'floor_number' => fake()->numberBetween(1, 10),
            'ad_type' => fake()->randomElement(['sale', 'rent']),
            'type' => fake()->randomElement(['apartment', 'villa', 'office', 'land', 'commercial', 'farm', 'building', 'chalet']),
            'status' => fake()->randomElement(['available', 'sold', 'rented']),
            'is_offer' => fake()->boolean(20),
            'offer_expires_at' => now()->addDays(fake()->numberBetween(1, 30)),
            'currency' => 'USD',
            'views' => fake()->numberBetween(0, 500),
            'bathrooms' => fake()->numberBetween(1, 5),
            'rooms' => fake()->numberBetween(1, 10),
            'seller_type' => fake()->randomElement(['owner', 'agent', 'developer']),
            'direction' => fake()->randomElement(['north', 'south', 'east', 'west']),
            'furnishing' => fake()->randomElement(['furnished', 'unfurnished', 'semi-furnished']),
            'features' => fake()->text,
        ];
    }
}
