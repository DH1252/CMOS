<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_all_as_read_returns_json_and_marks_unread_notifications(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_ANNOUNCEMENT,
            'title' => 'Notif 1',
            'message' => 'Pesan 1',
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_TASK_ASSIGNED,
            'title' => 'Notif 2',
            'message' => 'Pesan 2',
        ]);

        $alreadyRead = Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_EVALUATION_NEW,
            'title' => 'Notif 3',
            'message' => 'Pesan 3',
            'read_at' => now()->subMinute(),
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('notifications.mark-all-read'));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertSame(3, Notification::query()->where('user_id', $user->id)->whereNotNull('read_at')->count());
        $this->assertDatabaseHas('notifications', [
            'id' => $alreadyRead->id,
            'user_id' => $user->id,
        ]);
    }
}
