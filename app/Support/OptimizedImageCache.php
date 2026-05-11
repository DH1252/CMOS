<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class OptimizedImageCache
{
    /**
     * @var array<int, string>
     */
    public const SUPPORTED_FORMATS = ['avif', 'webp'];

    public const CACHE_DISK = 'public';

    public const CACHE_FOLDER = 'optimized-images';

    public const CACHE_HEADER = 'public, max-age=31536000, immutable';

    public const DEFAULT_MAX_WIDTH = 1600;

    public const DEFAULT_MAX_HEIGHT = 1600;

    /**
     * @var array<int, string>
     */
    public const OPTIMIZABLE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * @return array{path: string, format: string, cached: bool}|null
     */
    public function optimize(string $path, ?string $format, ?int $width, ?int $height, int $quality): ?array
    {
        $sourceDisk = Storage::disk('public');

        if (! $sourceDisk->exists($path)) {
            return null;
        }

        $cacheDisk = Storage::disk(self::CACHE_DISK);
        $originalFormat = $this->detectOriginalFormat($path);
        $requestedFormat = $format ?? $originalFormat;
        [$targetWidth, $targetHeight] = $this->resolveTargetDimensions($format, $width, $height);
        $lastModified = $sourceDisk->lastModified($path) ?: 0;

        foreach ($this->cacheLookupFormats($requestedFormat, $originalFormat) as $cachedFormat) {
            $cachePath = $this->cachePath($path, $cachedFormat, $targetWidth, $targetHeight, $quality, $lastModified);

            if ($cacheDisk->exists($cachePath)) {
                return [
                    'path' => $cachePath,
                    'format' => $cachedFormat,
                    'cached' => true,
                ];
            }
        }

        try {
            $image = Image::decodePath($sourceDisk->path($path));

            if ($targetWidth !== null || $targetHeight !== null) {
                $image = $image->scaleDown(width: $targetWidth, height: $targetHeight);
            }

            $outputFormat = $this->resolveOutputFormat($image, $requestedFormat, $originalFormat);
            $cachePath = $this->cachePath($path, $outputFormat, $targetWidth, $targetHeight, $quality, $lastModified);

            if ($cacheDisk->exists($cachePath)) {
                return [
                    'path' => $cachePath,
                    'format' => $outputFormat,
                    'cached' => true,
                ];
            }

            $cacheDisk->put($cachePath, $image->encodeUsingFileExtension($outputFormat, quality: $quality)->toString());

            return [
                'path' => $cachePath,
                'format' => $outputFormat,
                'cached' => false,
            ];
        } catch (Throwable) {
            return null;
        }
    }

    public function canOptimizePath(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, self::OPTIMIZABLE_EXTENSIONS, true)
            && ! str_starts_with(trim($path, '/'), self::CACHE_FOLDER.'/');
    }

    public function formatToMimeType(?string $format): string
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

    /**
     * @return array{0: int|null, 1: int|null}
     */
    public function resolveTargetDimensions(?string $format, ?int $width, ?int $height): array
    {
        if ($width !== null || $height !== null) {
            return [$width, $height];
        }

        if ($format !== null) {
            return [self::DEFAULT_MAX_WIDTH, self::DEFAULT_MAX_HEIGHT];
        }

        return [null, null];
    }

    public function cacheDisk(): FilesystemAdapter
    {
        return Storage::disk(self::CACHE_DISK);
    }

    private function resolveOutputFormat($image, string $requestedFormat, string $originalFormat): string
    {
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

    /**
     * @return array<int, string>
     */
    private function cacheLookupFormats(string $requestedFormat, string $originalFormat): array
    {
        if ($this->canEncode($requestedFormat)) {
            return [$requestedFormat];
        }

        return array_values(array_filter(
            $this->formatCandidates($requestedFormat, $originalFormat),
            fn (string $candidate): bool => $this->canEncode($candidate),
        ));
    }

    private function cachePath(string $path, string $format, ?int $width, ?int $height, int $quality, int $lastModified): string
    {
        $signature = md5($path.':'.$format.':'.$width.':'.$height.':'.$quality.':'.$lastModified);

        return self::CACHE_FOLDER.'/'.$signature.'.'.$format;
    }

    private function detectOriginalFormat(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg'], true) ? 'jpg' : $extension;
    }

    private function driverFormatIdentifier(string $format): string
    {
        return $format === 'jpg' ? 'jpeg' : $format;
    }

    private function canEncode(string $format): bool
    {
        return match ($this->driverFormatIdentifier($format)) {
            'jpeg' => (bool) (imagetypes() & IMG_JPEG),
            'png' => (bool) (imagetypes() & IMG_PNG),
            'gif' => (bool) (imagetypes() & IMG_GIF),
            'webp' => (bool) (imagetypes() & IMG_WEBP),
            'avif' => (bool) (imagetypes() & IMG_AVIF),
            default => false,
        };
    }
}
