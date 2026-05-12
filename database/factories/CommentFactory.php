<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            // IDs will be provided by the Seeder
            'userId' => \App\Models\Member::factory(),
            'sharedSolutionId' => null,
            'discussionId' => null,

            'content' => $this->faker->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
