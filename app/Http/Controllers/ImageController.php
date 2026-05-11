<?php

namespace App\Http\Controllers;

use App\Support\OptimizedImageCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageController extends Controller
{
    public function __construct(private readonly OptimizedImageCache $imageCache) {}

    public function show(Request $request, string $path): Response
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            throw new NotFoundHttpException('Image not found.');
        }

        $requestedFormat = $this->resolveFormat($request);
        $width = $this->resolveDimension($request->input('w'));
        $height = $this->resolveDimension($request->input('h'));
        $quality = $this->resolveQuality($request->input('q'));

        if ($requestedFormat === null && $width === null && $height === null) {
            return $this->serveOriginal($disk, $path);
        }

        $variant = $this->imageCache->optimize($path, $requestedFormat, $width, $height, $quality);

        if ($variant === null) {
            return $this->serveOriginal($disk, $path);
        }

        return $this->serveCached($variant['path'], $variant['format'], $variant['cached'] ? 'HIT' : 'MISS');
    }

    private function serveOriginal($disk, string $path): Response
    {
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        return new Response($disk->get($path), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => OptimizedImageCache::CACHE_HEADER,
            'X-Image-Optimized' => '0',
        ]);
    }

    private function serveCached(string $cachePath, string $format, string $cacheStatus = 'HIT'): BinaryFileResponse
    {
        $cacheDisk = $this->imageCache->cacheDisk();
        $mimeType = $this->imageCache->formatToMimeType($format);

        return response()->file($cacheDisk->path($cachePath), [
            'Content-Type' => $mimeType,
            'Cache-Control' => OptimizedImageCache::CACHE_HEADER,
            'X-Image-Optimized' => '1',
            'X-Image-Cache' => $cacheStatus,
        ]);
    }

    private function resolveFormat(Request $request): ?string
    {
        $format = strtolower((string) $request->input('f', ''));

        if ($format === '' || $format === 'original') {
            return null;
        }

        if (! in_array($format, OptimizedImageCache::SUPPORTED_FORMATS, true)) {
            return null;
        }

        return $format;
    }

    private function resolveDimension(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT);

        if ($int === false || $int < 1 || $int > 4096) {
            return null;
        }

        return $int;
    }

    private function resolveQuality(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 85;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT);

        if ($int === false || $int < 1 || $int > 100) {
            return 85;
        }

        return $int;
    }
}
