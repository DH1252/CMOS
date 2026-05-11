<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ImageController extends Controller
{
    private const SUPPORTED_FORMATS = ['webp', 'avif'];

    private const CACHE_DISK = 'local';

    private const CACHE_FOLDER = 'optimized-images';

    private const CACHE_HEADER = 'public, max-age=31536000, immutable';

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

        return $this->processAndServe($disk, $path, Storage::disk(self::CACHE_DISK), $requestedFormat, $width, $height, $quality);
    }

    private function serveOriginal($disk, string $path): Response
    {
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        return new Response($disk->get($path), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => self::CACHE_HEADER,
            'X-Image-Optimized' => '0',
        ]);
    }

    private function serveCached($cacheDisk, string $cachePath, string $format): Response
    {
        $mimeType = $this->formatToMimeType($format);

        return new Response($cacheDisk->get($cachePath), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => self::CACHE_HEADER,
            'X-Image-Optimized' => '1',
        ]);
    }

    private function processAndServe($disk, string $path, $cacheDisk, ?string $format, ?int $width, ?int $height, int $quality): Response
    {
        try {
            $originalPath = $disk->path($path);
            $image = Image::decodePath($originalPath);

            if ($width !== null || $height !== null) {
                $image = $image->scaleDown(width: $width, height: $height);
            }

            $outputFormat = $this->resolveOutputFormat($image, $format, $path);
            $cachePath = self::CACHE_FOLDER.'/'.$this->buildCacheKey(
                path: $path,
                format: $outputFormat,
                width: $width,
                height: $height,
                quality: $quality,
                lastModified: $disk->lastModified($path) ?: 0,
            );

            if ($cacheDisk->exists($cachePath)) {
                return $this->serveCached($cacheDisk, $cachePath, $outputFormat);
            }

            $encoded = $image->encodeUsingFileExtension($outputFormat, quality: $quality);
            $contents = $encoded->toString();

            $cacheDisk->put($cachePath, $contents);

            $mimeType = $this->formatToMimeType($outputFormat);

            return new Response($contents, 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => self::CACHE_HEADER,
                'X-Image-Optimized' => '1',
            ]);
        } catch (Throwable) {
            return $this->serveOriginal($disk, $path);
        }
    }

    private function resolveOutputFormat($image, ?string $format, string $path): string
    {
        $originalFormat = $this->detectOriginalFormat($path);
        $requestedFormat = $format ?? $originalFormat;

        foreach ($this->formatCandidates($requestedFormat, $originalFormat) as $candidate) {
            if ($image->driver()->supports($this->driverFormatIdentifier($candidate))) {
                return $candidate;
            }
        }

        return $originalFormat;
    }

    /**
     * @return array<int, string>
     */
    private function formatCandidates(string $requestedFormat, string $originalFormat): array
    {
        return match ($requestedFormat) {
            'avif' => array_values(array_unique(['avif', 'webp', $originalFormat])),
            'webp' => array_values(array_unique(['webp', $originalFormat])),
            default => [$originalFormat],
        };
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

    private function buildCacheKey(string $path, string $format, ?int $width, ?int $height, int $quality, int $lastModified): string
    {
        $info = pathinfo($path);
        $base = $info['dirname'].'/'.$info['filename'];
        $base = trim($base, '/');
        $signature = md5($base.':'.$format.':'.$width.':'.$height.':'.$quality.':'.$lastModified);

        return $signature.'.'.$format;
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

    private function driverFormatIdentifier(string $format): string
    {
        return $format === 'jpg' ? 'jpeg' : $format;
    }
}
