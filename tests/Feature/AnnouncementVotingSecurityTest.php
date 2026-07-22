<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementVotingSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_poll_rejects_an_option_from_another_announcement(): void
    {
        $user = User::factory()->createOne(['status' => 'active']);
        $firstPoll = $this->poll($user, 'First poll');
        $secondPoll = $this->poll($user, 'Second poll');
        $foreignOption = PollOption::create([
            'announcement_id' => $secondPoll->id,
            'option_text' => 'Foreign option',
        ]);

        $response = $this->actingAs($user)->postJson(route('announcements.vote', $firstPoll), [
            'option_id' => $foreignOption->id,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('option_id');
        $this->assertDatabaseCount('poll_votes', 0);
        $this->assertDatabaseHas('poll_options', [
            'id' => $foreignOption->id,
            'votes_count' => 0,
        ]);
    }

    public function test_user_cannot_vote_for_two_options_in_the_same_poll(): void
    {
        $author = User::factory()->createOne(['status' => 'active']);
        $voter = User::factory()->createOne(['status' => 'active']);
        $poll = $this->poll($author, 'Serialized poll');
        $firstOption = PollOption::create([
            'announcement_id' => $poll->id,
            'option_text' => 'First',
        ]);
        $secondOption = PollOption::create([
            'announcement_id' => $poll->id,
            'option_text' => 'Second',
        ]);

        $this->actingAs($voter)->postJson(route('announcements.vote', $poll), [
            'option_id' => $firstOption->id,
        ])->assertOk();

        $this->actingAs($voter)->postJson(route('announcements.vote', $poll), [
            'option_id' => $secondOption->id,
        ])->assertStatus(400)->assertJson(['error' => 'Anda sudah memilih']);

        $this->assertDatabaseCount('poll_votes', 1);
        $this->assertDatabaseHas('poll_options', ['id' => $firstOption->id, 'votes_count' => 1]);
        $this->assertDatabaseHas('poll_options', ['id' => $secondOption->id, 'votes_count' => 0]);
    }

    private function poll(User $author, string $question): Announcement
    {
        return Announcement::create([
            'user_id' => $author->id,
            'content' => $question,
            'has_poll' => true,
            'poll_question' => $question,
            'poll_ends_at' => now()->addHour(),
        ]);
    }
}
