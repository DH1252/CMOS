<?php

namespace Tests\Feature;

use App\Models\InformationBoard;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizationTest extends TestCase
{
    public function test_it_serves_original_image_when_no_format_requested(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('test.jpg', 100, 100);
        Storage::disk('public')->put('information-boards/test.jpg', $image->get());

        $response = $this->get(route('images.optimize', ['path' => 'information-boards/test.jpg']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=31536000', $cacheControl);
        $this->assertStringContainsString('immutable', $cacheControl);
    }

    public function test_it_serves_webp_when_requested(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $image = UploadedFile::fake()->image('test.jpg', 100, 100);
        Storage::disk('public')->put('information-boards/test.jpg', $image->get());

        $response = $this->get(route('images.optimize', ['path' => 'information-boards/test.jpg', 'f' => 'webp']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/webp');
    }

    public function test_it_returns_404_for_missing_image(): void
    {
        $response = $this->get(route('images.optimize', ['path' => 'nonexistent.jpg']));

        $response->assertNotFound();
    }

    public function test_it_ignores_invalid_dimensions(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('test.jpg', 500, 500);
        Storage::disk('public')->put('information-boards/test.jpg', $image->get());

        $response = $this->get(route('images.optimize', [
            'path' => 'information-boards/test.jpg',
            'w' => 'invalid',
            'h' => '-1',
            'f' => 'webp',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/webp');
    }

    public function test_it_respects_quality_parameter(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $image = UploadedFile::fake()->image('test.jpg', 100, 100);
        Storage::disk('public')->put('information-boards/test.jpg', $image->get());

        $response = $this->get(route('images.optimize', [
            'path' => 'information-boards/test.jpg',
            'f' => 'webp',
            'q' => 50,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/webp');
    }

    public function test_it_caches_converted_images(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $image = UploadedFile::fake()->image('test.jpg', 100, 100);
        Storage::disk('public')->put('information-boards/test.jpg', $image->get());

        $response1 = $this->get(route('images.optimize', [
            'path' => 'information-boards/test.jpg',
            'f' => 'webp',
        ]));
        $response1->assertOk();

        // Second request should be served from cache
        $response2 = $this->get(route('images.optimize', [
            'path' => 'information-boards/test.jpg',
            'f' => 'webp',
        ]));
        $response2->assertOk();
    }

    public function test_it_rewrites_stored_content_images_to_optimized_urls(): void
    {
        $article = new InformationBoard([
            'content' => '<p><img src="/storage/information-boards/attachments/example.png" alt="Example"></p>',
        ]);

        $this->assertStringContainsString(
            '/images/optimize/information-boards/attachments/example.png?f=webp',
            $article->content_optimized,
        );
        $this->assertStringContainsString('loading="lazy"', $article->content_optimized);
        $this->assertStringContainsString('decoding="async"', $article->content_optimized);
    }
}
