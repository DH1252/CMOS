<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Support\RealtimeBroadcaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RealtimeSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_realtime_snapshot_returns_live_channel_signatures_and_counts(): void
    {
        ['user' => $user, 'colleague' => $colleague] = $this->createContext();

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_TASK_ASSIGNED,
            'title' => 'Task baru',
            'message' => 'Ada task yang perlu dicek.',
            'data' => ['task_id' => 99],
        ]);

        Message::create([
            'sender_id' => $colleague->id,
            'receiver_id' => $user->id,
            'content' => 'Halo realtime',
        ]);

        Announcement::create([
            'user_id' => $colleague->id,
            'content' => 'Pengumuman baru untuk feed.',
            'has_poll' => false,
        ]);

        Task::create([
            'title' => 'Global realtime task',
            'description' => 'Task untuk menguji snapshot realtime.',
            'program_id' => null,
            'department_id' => null,
            'assigned_to' => null,
            'created_by' => $user->id,
            'status' => 'todo',
            'sort_order' => 1,
            'progress' => 0,
            'priority' => 'medium',
            'deadline' => now()->addDay()->toDateString(),
            'is_global' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('realtime.snapshot'));

        $response->assertOk();
        $response->assertJsonStructure([
            'meta' => ['generatedAt'],
            'channels' => [
                'messages' => ['signature', 'unreadCount'],
                'notifications' => ['signature', 'unreadCount'],
                'announcements' => ['signature'],
                'tasks' => ['signature'],
            ],
        ]);
        $response->assertJsonPath('channels.messages.unreadCount', 1);
        $response->assertJsonPath('channels.notifications.unreadCount', 1);
    }

    public function test_notifications_index_returns_json_snapshot_for_live_refresh(): void
    {
        ['user' => $user] = $this->createContext();

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_EVALUATION_NEW,
            'title' => 'Evaluasi masuk',
            'message' => 'Ada evaluasi baru bulan ini.',
        ]);

        $response = $this->actingAs($user)->getJson(route('notifications.index'));

        $response->assertOk();
        $response->assertJsonPath('unreadCount', 1);
        $response->assertJsonCount(1, 'notifications');
        $response->assertJsonStructure([
            'notifications' => [
                ['id', 'title', 'message', 'icon', 'tone', 'href', 'readAt', 'createdAt', 'readUrl', 'deleteUrl'],
            ],
            'pagination' => ['currentPage', 'lastPage', 'previousUrl', 'nextUrl', 'markAllUrl'],
        ]);
    }

    public function test_announcements_index_returns_json_snapshot_for_live_refresh(): void
    {
        ['user' => $user, 'colleague' => $colleague] = $this->createContext();

        Announcement::create([
            'user_id' => $colleague->id,
            'content' => 'Pengumuman realtime',
            'has_poll' => false,
        ]);

        $response = $this->actingAs($user)->getJson(route('announcements.index'));

        $response->assertOk();
        $response->assertJsonCount(1, 'posts');
        $response->assertJsonPath('posts.0.content', 'Pengumuman realtime');
        $response->assertJsonPath('pagination.total', 1);
    }

    public function test_global_task_board_returns_json_snapshot_for_live_refresh(): void
    {
        ['user' => $user] = $this->createContext();

        Task::create([
            'title' => 'Task board live refresh',
            'description' => 'Task global untuk board realtime.',
            'program_id' => null,
            'department_id' => null,
            'assigned_to' => null,
            'created_by' => $user->id,
            'status' => 'todo',
            'sort_order' => 1,
            'progress' => 0,
            'priority' => 'high',
            'deadline' => now()->addDays(2)->toDateString(),
            'is_global' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('tasks.global'));

        $response->assertOk();
        $response->assertJsonPath('context.type', 'global');
        $response->assertJsonPath('refreshUrl', route('tasks.global'));
        $response->assertJsonFragment(['title' => 'Task board live refresh']);
    }

    public function test_sending_message_triggers_websocket_broadcast_helper(): void
    {
        ['user' => $user, 'colleague' => $colleague] = $this->createContext();

        $broadcaster = \Mockery::mock(RealtimeBroadcaster::class);
        $broadcaster->shouldReceive('chatMessage')->once();
        $this->app->instance(RealtimeBroadcaster::class, $broadcaster);

        $response = $this->actingAs($user)->postJson(route('messages.send', $colleague), [
            'content' => 'Halo via websocket',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('messages', [
            'sender_id' => $user->id,
            'receiver_id' => $colleague->id,
            'content' => 'Halo via websocket',
        ]);
    }

    public function test_chat_message_broadcast_writes_reverb_logs(): void
    {
        ['user' => $user, 'colleague' => $colleague] = $this->createContext();

        $message = Message::withoutEvents(function () use ($user, $colleague) {
            return Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $colleague->id,
                'content' => 'Pesan untuk log reverb',
            ]);
        });

        Log::shouldReceive('channel')->twice()->with('reverb')->andReturnSelf();
        Log::shouldReceive('info')->once()->with('Broadcasting chat message over Reverb', \Mockery::on(function (array $context) use ($message, $user, $colleague): bool {
            return $context['message_id'] === $message->id
                && $context['sender_id'] === $user->id
                && $context['receiver_id'] === $colleague->id
                && $context['content_length'] === mb_strlen('Pesan untuk log reverb');
        }));
        Log::shouldReceive('info')->once()->with('Broadcasted chat message over Reverb', \Mockery::on(function (array $context) use ($message, $user, $colleague): bool {
            return $context['message_id'] === $message->id
                && $context['sender_id'] === $user->id
                && $context['receiver_id'] === $colleague->id;
        }));

        app(RealtimeBroadcaster::class)->chatMessage($message);
    }

    /**
     * @return array{user: User, colleague: User, role: Role, department: Department, cabinet: Cabinet}
     */
    private function createContext(): array
    {
        $role = Role::create([
            'name' => 'staff',
            'description' => 'Staff',
        ]);

        $cabinet = Cabinet::create([
            'name' => 'Kabinet Realtime',
            'year' => '2026/2027',
            'status' => 'active',
        ]);

        $department = Department::create([
            'name' => 'Ristek',
            'description' => 'Departemen Ristek',
            'cabinet_id' => $cabinet->id,
            'status' => 'active',
        ]);

        $user = User::factory()->createOne([
            'name' => 'Realtime User',
            'role_id' => $role->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        $colleague = User::factory()->createOne([
            'name' => 'Realtime Colleague',
            'role_id' => $role->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        return compact('user', 'colleague', 'role', 'department', 'cabinet');
    }
}
