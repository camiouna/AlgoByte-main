<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            // IDs will be provided by the Seeder
            'reporterId' => \App\Models\Member::factory(),
            'reviewerId' => null, // Typically null until resolved
            'problemId' => \App\Models\Problem::factory(),

            'reason' => $this->faker->randomElement([
                'Incorrect test cases for the problem.',
                'Plagiarized description from another platform.',
                'Offensive or inappropriate language in the explanation.',
                'Technical bug preventing submission.',
                'Missing constraints in the description.'
            ]),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high']),
            'status' => $this->faker->randomElement(['pending', 'reviewed', 'resolved']),
            'created_at' => $this->faker->dateTimeBetween('-3 weeks', 'now'),
        ];
    }
}
