<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\Member;
use App\Models\Problem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition(): array
    {
        return [
            // Relationships
            'userId' => Member::factory(),
            'problemId' => Problem::factory(),

            // Core Data
            'code' => "function solve() {\n    // " . $this->faker->sentence() . "\n    return true;\n}",
            'language' => $this->faker->randomElement(['javascript', 'python', 'java', 'cpp']),
            'status' => $this->faker->randomElement(['Accepted', 'Wrong Answer', 'Time Limit Exceeded', 'Runtime Error', 'received']),

            // Execution Metadata (Matched to merged migration)
            'stdout' => $this->faker->optional(0.7)->paragraph(),
            'stderr' => $this->faker->optional(0.1)->sentence(),
            'compile_output' => null,
            'exit_code' => $this->faker->randomElement([0, 0, 0, 1]), // 0 is success
            'execution_time_ms' => $this->faker->numberBetween(50, 1500),
            'runtime' => $this->faker->randomElement(['node', 'python3', 'openjdk']),
            'runtime_version' => '1.0.0',
            'signal' => null,

            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }
}
