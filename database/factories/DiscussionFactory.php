<?php

namespace Database\Factories;

use App\Models\Discussion;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscussionFactory extends Factory
{
    protected $model = Discussion::class;

    public function definition(): array
    {
        return [
            // IDs will be passed from the Seeder to ensure referential integrity
            'userId' => \App\Models\Member::factory(),
            'problemId' => \App\Models\Problem::factory(),

            'title' => $this->faker->sentence() . "?",
            'content' => $this->faker->paragraphs(3, true),
            'upvotes' => $this->faker->numberBetween(0, 100),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
