<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class MicroServiceMapFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => Str::slug('_', $name),
            'display_name' => \ucfirst($name),
            'origin' => 'http://localhost:8001',
            'destination_key' => $this->faker->number(100000000, 999999999),
        ];
    }
}
