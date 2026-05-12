<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Problem;
use App\Models\Submission;
use App\Models\SharedSolution;
use App\Models\Discussion;
use App\Models\Comment;
use App\Models\LikeActivity;
use App\Models\Report; // Import the Report model
use Illuminate\Database\Seeder;
use App\Models\TestCase;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        dump("Generating Members...");
        $members = Member::factory(10)->create();

        // Identify some admins for the reviewer roles
        $admins = Member::factory(3)->create(['is_admin' => true]);

        dump("Generating Problems...");
        $problems = Problem::factory(15)->create([
            'creatorId' => fn() => $members->random()->userId,
        ]);

       dump("Generating Test Cases for each problem...");
        $problems->each(function ($problem) {
            // Create 2 Sample Test Cases (Visible to users)
            TestCase::factory(2)->create([
                'problemId' => $problem->problemId,
                'is_default' => true,
            ]);

            // Create 3 Hidden Test Cases (Used for evaluation)
            TestCase::factory(3)->create([
                'problemId' => $problem->problemId,
                'is_default' => false,
            ]);
        });

        dump("Generating Submissions...");
        $submissions = Submission::factory(50)->create([
            'userId' => fn() => $members->random()->userId,
            'problemId' => fn() => $problems->random()->problemId,
        ]);

        dump("Generating Shared Solutions...");
        $solutions = SharedSolution::factory(10)->create([
            'userId' => fn() => $members->random()->userId,
            'problemId' => fn() => $problems->random()->problemId,
            'submissionId' => fn() => $submissions->random()->submissionId,
        ]);

        dump("Generating Discussions...");
        $discussions = Discussion::factory(20)->create([
            'userId' => fn() => $members->random()->userId,
            'problemId' => fn() => $problems->random()->problemId,
        ]);

        dump("Generating Comments...");
        $discussionComments = Comment::factory(20)->create([
            'userId' => fn() => $members->random()->userId,
            'discussionId' => fn() => $discussions->random()->discussionId,
        ]);

        // NEW: Generate Likes (LikeActivity)
        dump("Generating Likes across the platform...");

        // Likes for Discussions
        LikeActivity::factory(30)->create([
            'userId' => fn() => $members->random()->userId,
            'discussionId' => fn() => $discussions->random()->discussionId,
        ]);

        // Likes for Shared Solutions
        LikeActivity::factory(20)->create([
            'userId' => fn() => $members->random()->userId,
            'sharedSolutionId' => fn() => $solutions->random()->solutionId,
        ]);

        // Likes for Comments
        LikeActivity::factory(15)->create([
            'userId' => fn() => $members->random()->userId,
            'commentId' => fn() => $discussionComments->random()->commentId,
        ]);

        // NEW: Generate 10 Reports
        dump("Generating Moderation Reports...");
        Report::factory(10)->create([
            'reporterId' => fn() => $members->random()->userId,
            'problemId' => fn() => $problems->random()->problemId,
            'reviewerId' => function (array $attributes) use ($admins) {
                // If status isn't pending, assign an admin as reviewer
                return $attributes['status'] !== 'pending' ? $admins->random()->userId : null;
            },
        ]);

        dump("Seeding complete! The moderation queue is now populated.");
    }
}
