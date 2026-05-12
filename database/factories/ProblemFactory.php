<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProblemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'creatorId' => Member::factory(), // This creates a new member if one isn't provided
            'title' => fake()->words(3, true),
            'description' => "### The Challenge \n" . fake()->paragraphs(2, true) . "\n\n **Constraints:** \n - " . fake()->sentence(),
            'difficulty' => fake()->randomElement(['Easy', 'Medium', 'Hard']),
            'status' => 'published',
            'visibility' => 'public',
            'solution' => 'function solve(n) { return n; }',
            'explanation' => fake()->sentence(),
        ];
    }
}
