<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageController extends Controller
{
    private const SUPPORTED_FORMATS = ['webp', 'avif'];

    private const CACHE_DISK = 'local';

    private const CACHE_FOLDER = 'optimized-images';

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

        $cacheKey = $this->buildCacheKey($path, $requestedFormat, $width, $height, $quality);
        $cacheDisk = Storage::disk(self::CACHE_DISK);
        $cachePath = self::CACHE_FOLDER.'/'.$cacheKey;

        if ($cacheDisk->exists($cachePath)) {
            return $this->serveCached($cacheDisk, $cachePath, $requestedFormat);
        }

        return $this->processAndServe($disk, $path, $cacheDisk, $cachePath, $requestedFormat, $width, $height, $quality);
    }

    private function serveOriginal($disk, string $path): Response
    {
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        return new Response($disk->get($path), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function serveCached($cacheDisk, string $cachePath, ?string $format): Response
    {
        $mimeType = $this->formatToMimeType($format);

        return new Response($cacheDisk->get($cachePath), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function processAndServe($disk, string $path, $cacheDisk, string $cachePath, ?string $format, ?int $width, ?int $height, int $quality): Response
    {
        $originalPath = $disk->path($path);
        $image = Image::decodePath($originalPath);

        if ($width !== null || $height !== null) {
            $image = $image->scaleDown($width, $height);
        }

        $outputFormat = $format ?? $this->detectOriginalFormat($path);
        if ($outputFormat === 'avif' && ! $image->driver()->supports('avif')) {
            $outputFormat = 'webp';
        }

        $encoded = match ($outputFormat) {
            'webp' => $image->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: $quality)),
            'avif' => $image->encode(new \Intervention\Image\Encoders\AvifEncoder(quality: $quality)),
            default => $image->encode(),
        };

        if ($outputFormat === ($format ?? $this->detectOriginalFormat($path))) {
            $cacheDisk->put($cachePath, $encoded->toString());
        }

        $mimeType = $this->formatToMimeType($outputFormat);

        return new Response($encoded->toString(), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function resolveFormat(Request $request): ?string
    {
        $format = strtolower((string) $request->input('f', ''));

        if ($format === '' || $format === 'original') {
            return null;
        }

        if (! in_array($format, self::SUPPORTED_FORMATS, true)) {
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

    private function buildCacheKey(string $path, ?string $format, ?int $width, ?int $height, int $quality): string
    {
        $info = pathinfo($path);
        $base = $info['dirname'].'/'.$info['filename'];
        $base = trim($base, '/');
        $signature = md5($base.':'.$format.':'.$width.':'.$height.':'.$quality);
        $extension = $format ?? ($info['extension'] ?? 'bin');

        return $signature.'.'.$extension;
    }

    private function detectOriginalFormat(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg'], true) ? 'jpg' : $extension;
    }

    private function formatToMimeType(?string $format): string
    {
        return match ($format) {
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };
    }
}
