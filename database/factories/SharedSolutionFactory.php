<?php

namespace Database\Factories;

use App\Models\SharedSolution;
use Illuminate\Database\Eloquent\Factories\Factory;

class SharedSolutionFactory extends Factory
{
    protected $model = SharedSolution::class;

    public function definition(): array
    {
        return [
            // IDs will be provided by the Seeder to ensure valid relationships
            'userId' => \App\Models\Member::factory(),
            'problemId' => \App\Models\Problem::factory(),
            'submissionId' => null, // Optional source

            'title' => $this->faker->catchPhrase() . " Solution",
            'code' => "class Solution {\n    public function solve() {\n        // Optimized approach\n    }\n}",
            'explanation' => "### Approach\n" . $this->faker->paragraphs(2, true) . "\n\n### Complexity\n- Time: O(n)\n- Space: O(1)",
            'created_at' => $this->faker->dateTimeBetween('-2 weeks', 'now'),
        ];
    }
}
