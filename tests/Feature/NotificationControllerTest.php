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

    public function test_clear_all_returns_json_and_deletes_only_authenticated_user_notifications(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();
        /** @var User $anotherUser */
        $anotherUser = User::factory()->createOne();

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_ANNOUNCEMENT,
            'title' => 'Notif user 1',
            'message' => 'Pesan user 1',
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_TASK_ASSIGNED,
            'title' => 'Notif user 2',
            'message' => 'Pesan user 2',
        ]);

        Notification::create([
            'user_id' => $anotherUser->id,
            'type' => Notification::TYPE_EVALUATION_NEW,
            'title' => 'Notif user lain',
            'message' => 'Pesan user lain',
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('notifications.clear-all'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'deleted' => 2,
        ]);

        $this->assertSame(0, Notification::query()->where('user_id', $user->id)->count());
        $this->assertSame(1, Notification::query()->where('user_id', $anotherUser->id)->count());
    }
}
