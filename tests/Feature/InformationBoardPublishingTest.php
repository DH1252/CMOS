<?php

namespace Tests\Feature;

use App\Models\InformationBoard;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InformationBoardPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_article_can_be_posted_immediately(): void
    {
        $this->seed();

        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('information-boards.store'), [
            'title' => 'Artikel Segera Tayang',
            'excerpt' => 'Ringkasan singkat.',
            'content' => '<p>Konten artikel.</p>',
            'status' => 'published',
            'publish_mode' => 'immediately',
        ]);

        $response->assertRedirect(route('information-boards.index'));

        $article = InformationBoard::query()->where('title', 'Artikel Segera Tayang')->firstOrFail();

        $this->assertSame('published', $article->status);
        $this->assertNotNull($article->published_at);
        $this->assertSame('UTC', $article->published_at->getTimezone()->getName());
        $this->assertTrue($article->published_at->lte(now('UTC')));
    }

    public function test_published_article_can_be_scheduled_using_client_timezone(): void
    {
        $this->seed();

        $user = $this->adminUser();
        config()->set('app.client_timezone', 'Asia/Jakarta');

        $response = $this->actingAs($user)->post(route('information-boards.store'), [
            'title' => 'Artikel Terjadwal',
            'excerpt' => 'Ringkasan singkat.',
            'content' => '<p>Konten artikel.</p>',
            'status' => 'published',
            'publish_mode' => 'scheduled',
            'published_at' => '2026-04-14T09:30',
        ]);

        $response->assertRedirect(route('information-boards.index'));

        $article = InformationBoard::query()->where('title', 'Artikel Terjadwal')->firstOrFail();

        $this->assertSame('published', $article->status);
        $this->assertSame(
            '2026-04-14 02:30:00',
            $article->published_at?->copy()->setTimezone('UTC')->format('Y-m-d H:i:s'),
        );
    }

    public function test_scheduled_publish_requires_datetime(): void
    {
        $this->seed();

        $user = $this->adminUser();

        $response = $this->actingAs($user)->from(route('information-boards.create'))->post(route('information-boards.store'), [
            'title' => 'Artikel Tanpa Jadwal',
            'excerpt' => 'Ringkasan singkat.',
            'content' => '<p>Konten artikel.</p>',
            'status' => 'published',
            'publish_mode' => 'scheduled',
            'published_at' => '',
        ]);

        $response->assertRedirect(route('information-boards.create'));
        $response->assertSessionHasErrors('published_at');
    }

    public function test_article_attachment_upload_returns_trix_payload(): void
    {
        $this->seed();

        Storage::fake('public');
        $user = $this->adminUser();

        $response = $this->actingAs($user)->postJson(route('information-boards.attachments.upload'), [
            'attachment' => UploadedFile::fake()->image('lampiran.jpg', 1200, 800),
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'url',
                'href',
                'path',
                'filename',
                'filesize',
                'contentType',
            ]);

        $path = $response->json('path');
        $this->assertIsString($path);
        $this->assertTrue(Storage::disk('public')->exists($path));
    }

    private function adminUser(): User
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();

        return User::query()->where('role_id', $role->id)->firstOrFail();
    }
}
