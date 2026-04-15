<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\DriveAccount;
use App\Models\Evaluation;
use App\Models\InformationBoard;
use App\Models\InformationCategory;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Program;
use App\Models\Task;
use App\Models\Timeline;
use App\Models\UsefulLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_builds_rich_development_dataset(): void
    {
        $this->seed();

        $this->assertDatabaseCount('roles', 4);
        $this->assertDatabaseCount('departments', 5);
        $this->assertDatabaseCount('settings', 4);
        $this->assertDatabaseCount('grade_parameters', 5);
        $this->assertDatabaseCount('evaluation_criteria', 6);
        $this->assertDatabaseCount('information_categories', 4);

        $this->assertSame(18, User::query()->count());
        $this->assertSame(5, Program::query()->count());
        $this->assertSame(8, Task::query()->count());
        $this->assertSame(4, Timeline::query()->count());
        $this->assertSame(5, DriveAccount::query()->count());
        $this->assertSame(6, UsefulLink::query()->count());
        $this->assertSame(2, Announcement::query()->count());
        $this->assertSame(8, Message::query()->count());
        $this->assertSame(40, Evaluation::query()->count());
        $this->assertSame(3, InformationBoard::query()->count());
        $this->assertSame(4, ActivityLog::query()->count());

        $this->assertDatabaseHas('users', [
            'email' => 'admin@savana.test',
            'name' => 'Administrator',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'staff.inactive@savana.test',
            'status' => 'inactive',
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Review kalender konten mingguan',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('announcements', [
            'poll_question' => 'Format gathering internal yang paling cocok?',
            'has_poll' => true,
        ]);

        $this->assertSame(4, InformationCategory::query()->count());

        $this->assertDatabaseHas('settings', [
            'key' => 'theme_color',
            'value' => 'purple',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'evaluation_period',
            'value' => 'quarterly',
        ]);

        $this->assertDatabaseHas('messages', [
            'content' => 'Pastikan notifikasi evaluasi terbaru sudah dibaca sebelum standup.',
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => Notification::TYPE_ANNOUNCEMENT,
            'title' => 'Pengumuman Baru',
        ]);

        $this->assertGreaterThan(0, Notification::query()->count());
    }
}
