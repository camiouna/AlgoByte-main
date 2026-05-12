<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
{
    $avatarPath = 'profiles/' . Str::random(10) . '.jpg';
    $backgroundPath = 'backgrounds/' . Str::random(10) . '.jpg';

    try {
        // Fetch content from external service
        $avatarContent = file_get_contents('https://picsum.photos/200/200');
        $backgroundContent = file_get_contents('https://picsum.photos/1200/400');

        // Upload to Supabase
        Storage::disk('supabase')->put($avatarPath, $avatarContent);
        Storage::disk('supabase')->put($backgroundPath, $backgroundContent);

        // Use Laravel's built-in method to get the full public URL
        $profileImageUrl = Storage::disk('supabase')->url($avatarPath);
        $backgroundImageUrl = Storage::disk('supabase')->url($backgroundPath);

    } catch (\Exception $e) {
        dump("Upload failed: " . $e->getMessage());
        $profileImageUrl = 'https://via.placeholder.com/200';
        $backgroundImageUrl = 'https://via.placeholder.com/1200x400';
    }

    return [
        'username' => $this->faker->unique()->userName,
        'email' => $this->faker->unique()->safeEmail,
        'password' => Hash::make('password'),
        'profile_image' => $profileImageUrl,
        'background_image' => $backgroundImageUrl,

        // Authorization
            'is_admin' => false,

            // Stats
            'problems_solved' => $this->faker->numberBetween(0, 500),
            'problem_streak' => $this->faker->numberBetween(0, 30),
            'problems_created' => $this->faker->numberBetween(0, 10),
            'rank' => $this->faker->randomElement(['Newbie', 'Apprentice', 'Master', 'Grandmaster']),

            // Preferences
            'preferred_language' => $this->faker->randomElement(['javascript', 'python', 'java', 'cpp']),
            'editor_font_size' => $this->faker->randomElement([12, 14, 16, 18]),
            'theme' => $this->faker->randomElement(['dark', 'light', 'dracula', 'monokai']),

            'created_at' => now(),
            'updated_at' => now(),
    ];
}
}
