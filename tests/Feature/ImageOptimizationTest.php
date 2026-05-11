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
        $image = UploadedFile::fake()->image('test.jpg', 100, 100);
        Storage::disk('public')->put('information-boards/test.jpg', $image->get());

        $response = $this->get(route('images.optimize', ['path' => 'information-boards/test.jpg', 'f' => 'webp']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/webp');
        $response->assertHeader('X-Image-Optimized', '1');
    }

    public function test_it_resizes_and_caches_optimized_images(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('wide.jpg', 800, 400);
        Storage::disk('public')->put('information-boards/wide.jpg', $image->get());

        $route = route('images.optimize', [
            'path' => 'information-boards/wide.jpg',
            'f' => 'webp',
            'w' => 320,
        ]);

        $response = $this->get($route);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/webp');
        $response->assertHeader('X-Image-Optimized', '1');
        $response->assertHeader('X-Image-Cache', 'MISS');
        $response->assertHeaderMissing('Set-Cookie');
        $dimensions = getimagesize($response->baseResponse->getFile()->getPathname());
        $this->assertIsArray($dimensions);
        $this->assertSame(320, $dimensions[0]);
        $this->assertSame(160, $dimensions[1]);

        $cachedResponse = $this->get($route);

        $cachedResponse->assertOk();
        $cachedResponse->assertHeader('Content-Type', 'image/webp');
        $cachedResponse->assertHeader('X-Image-Optimized', '1');
        $cachedResponse->assertHeader('X-Image-Cache', 'HIT');
        $this->assertNotEmpty(Storage::disk('public')->files('optimized-images'));
    }

    public function test_it_auto_scales_format_conversions_without_dimensions(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('large.jpg', 2400, 1200);
        Storage::disk('public')->put('information-boards/large.jpg', $image->get());

        $response = $this->get(route('images.optimize', [
            'path' => 'information-boards/large.jpg',
            'f' => 'webp',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/webp');
        $response->assertHeader('X-Image-Cache', 'MISS');
        $dimensions = getimagesize($response->baseResponse->getFile()->getPathname());
        $this->assertIsArray($dimensions);
        $this->assertSame(1600, $dimensions[0]);
        $this->assertSame(800, $dimensions[1]);
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
        $image = UploadedFile::fake()->image('test.jpg', 100, 100);
        Storage::disk('public')->put('information-boards/test.jpg', $image->get());

        $response1 = $this->get(route('images.optimize', [
            'path' => 'information-boards/test.jpg',
            'f' => 'webp',
        ]));
        $response1->assertOk();

        $response2 = $this->get(route('images.optimize', [
            'path' => 'information-boards/test.jpg',
            'f' => 'webp',
        ]));
        $response2->assertOk();
        $response2->assertHeader('X-Image-Cache', 'HIT');
    }

    public function test_it_prioritizes_avif_requests_and_falls_back_when_needed(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('test.jpg', 500, 250);
        Storage::disk('public')->put('information-boards/test.jpg', $image->get());

        $response = $this->get(route('images.optimize', [
            'path' => 'information-boards/test.jpg',
            'f' => 'avif',
            'w' => 320,
        ]));

        $response->assertOk();
        $response->assertHeader('X-Image-Optimized', '1');
        $this->assertContains($response->headers->get('Content-Type'), ['image/avif', 'image/webp']);
    }

    public function test_it_warms_optimized_image_cache_for_repeat_requests(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('wide.jpg', 800, 400);
        Storage::disk('public')->put('information-boards/wide.jpg', $image->get());

        $this->artisan('images:warm-optimized', [
            'path' => 'information-boards',
            '--format' => ['avif'],
            '--width' => [320],
        ])->assertExitCode(0);

        $response = $this->get(route('images.optimize', [
            'path' => 'information-boards/wide.jpg',
            'f' => 'avif',
            'w' => 320,
        ]));

        $response->assertOk();
        $response->assertHeader('X-Image-Optimized', '1');
        $response->assertHeader('X-Image-Cache', 'HIT');
    }

    public function test_it_serves_original_when_optimization_fails(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('information-boards/corrupt.jpg', 'not an image');

        $response = $this->get(route('images.optimize', [
            'path' => 'information-boards/corrupt.jpg',
            'f' => 'webp',
            'w' => 320,
        ]));

        $response->assertOk();
        $this->assertSame('not an image', $response->getContent());
        $response->assertHeader('X-Image-Optimized', '0');
    }

    public function test_it_rewrites_stored_content_images_to_optimized_urls(): void
    {
        $article = new InformationBoard([
            'content' => '<p><img src="/storage/information-boards/attachments/example.png" alt="Example"></p>',
        ]);

        $this->assertStringContainsString(
            '/images/optimize/information-boards/attachments/example.png?f=avif',
            $article->content_optimized,
        );
        $this->assertStringContainsString('loading="lazy"', $article->content_optimized);
        $this->assertStringContainsString('decoding="async"', $article->content_optimized);
        $this->assertStringContainsString('style="max-width: 100%; height: auto; object-fit: contain;"', $article->content_optimized);
    }

    public function test_it_exposes_avif_before_webp_for_cover_images(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('cover.jpg', 800, 400);
        Storage::disk('public')->put('information-boards/cover.jpg', $image->get());

        $article = new InformationBoard([
            'cover_image' => 'information-boards/cover.jpg',
        ]);

        $this->assertSame(
            route('images.optimize', ['path' => 'information-boards/cover.jpg']).'?f=avif',
            $article->cover_image_optimized['avif'],
        );
        $this->assertSame(
            route('images.optimize', ['path' => 'information-boards/cover.jpg']).'?f=webp',
            $article->cover_image_optimized['webp'],
        );
    }
}
