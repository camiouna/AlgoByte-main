<?php

namespace Database\Factories;

use App\Models\LikeActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class LikeActivityFactory extends Factory
{
    protected $model = LikeActivity::class;

    public function definition(): array
    {
        return [
            // User who performed the action
            'userId' => \App\Models\Member::factory(),

            // These will be assigned specifically by the seeder
            'sharedSolutionId' => null,
            'discussionId' => null,
            'commentId' => null,

            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
