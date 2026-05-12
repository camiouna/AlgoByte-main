<?php

namespace Database\Factories;

use App\Models\TestCase;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestCaseFactory extends Factory
{
    protected $model = TestCase::class;

    public function definition(): array
    {
        return [
            // problemId will be provided by the Seeder
            'problemId' => \App\Models\Problem::factory(),

            // Simulating coding problem inputs/outputs
            'input' => $this->faker->randomElement([
                "[1, 2, 3, 4, 5]",
                "\"racecar\"",
                "7, 14",
                "{\"key\": \"value\"}"
            ]),
            'expected_output' => $this->faker->randomElement([
                "15",
                "true",
                "21",
                "\"value\""
            ]),
            'is_default' => false, // Default to hidden/private test case
            'created_at' => now(),
        ];
    }
}
