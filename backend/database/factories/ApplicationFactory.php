<?php

namespace Database\Factories;

use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company' => fake()->company(),
            'position' => fake()->jobTitle(),
            'status' => fake()->randomElement(Application::STATUSES),
            'deadline' => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
        ];
    }
}