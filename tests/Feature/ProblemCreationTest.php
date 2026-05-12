<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Problem;
use App\Models\TestCase as ProblemTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProblemCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_create_a_problem_with_two_validated_examples(): void
    {
        $member = Member::create([
            'username' => 'creator',
            'email' => 'creator@example.com',
            'password' => Hash::make('password'),
            'preferred_language' => 'typescript',
            'theme' => 'dark',
            'editor_font_size' => 14,
        ]);

        $response = $this->actingAs($member)->postJson('/problemcreation', [
            'title' => 'Two Sum',
            'description' => 'Return the indices of two numbers that add up to the target.',
            'language' => 'typescript',
            'difficulty' => 'easy',
            'solution' => "function solution(nums, target) {\n  return [0, 1]\n}",
            'explanation' => 'Use a hash map to track complements while iterating once.',
            'visibility' => 'private',
            'test_cases' => [
                [
                    'label' => 'Example 01',
                    'arguments' => ['[2,7,11,15]', '9'],
                    'input' => '[2,7,11,15], 9',
                    'expected_output' => '[0,1]',
                    'status' => 'success',
                ],
                [
                    'label' => 'Example 02',
                    'arguments' => ['[3,2,4]', '6'],
                    'input' => '[3,2,4], 6',
                    'expected_output' => '[1,2]',
                    'status' => 'success',
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Problem created successfully.')
            ->assertJsonPath('data.problem.title', 'Two Sum')
            ->assertJsonPath('data.problem.language', 'typescript')
            ->assertJsonPath('data.problem.visibility', 'private')
            ->assertJsonPath('data.problem.test_case_count', 2);

        $problem = Problem::firstOrFail();

        $this->assertSame('Two Sum', $problem->title);
        $this->assertSame('typescript', $problem->language);
        $this->assertSame('private', $problem->visibility);
        $this->assertSame($member->userId, $problem->creatorId);
        $this->assertSame(2, ProblemTestCase::where('problemId', $problem->problemId)->count());

        $this->assertDatabaseHas('test_cases', [
            'problemId' => $problem->problemId,
            'expected_output' => '[0,1]',
            'is_default' => true,
        ]);

        $this->assertSame(1, $member->fresh()->problems_created);
    }
}
