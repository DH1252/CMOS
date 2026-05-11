<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_avatar_upload_is_stored_as_optimized_webp(): void
    {
        $this->seed();

        Storage::fake('public');

        $role = Role::query()->where('name', 'staff')->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Optimized Avatar User',
            'avatar' => UploadedFile::fake()->image('avatar.png', 800, 800),
        ]);

        $response->assertRedirect();

        $user->refresh();

        $this->assertIsString($user->avatar);
        $this->assertStringEndsWith('.webp', $user->avatar);
        $this->assertTrue(Storage::disk('public')->exists('avatars/'.$user->avatar));
        $this->assertStringContainsString('/images/optimize/avatars/', $user->avatar_url);
        $this->assertStringContainsString('f=webp', $user->avatar_url);
    }
}
