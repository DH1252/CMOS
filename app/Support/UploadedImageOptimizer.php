<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;
use Throwable;

class UploadedImageOptimizer
{
    /**
     * @var array<int, string>
     */
    private const OPTIMIZABLE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/avif',
        'image/webp',
    ];

    /**
     * @var array<string, string>
     */
    private const OPTIMIZED_CONTENT_TYPES = [
        'avif' => 'image/avif',
        'webp' => 'image/webp',
    ];

    /**
     * @return array{path: string, size: int, contentType: string}
     */
    public function store(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1600,
        int $maxHeight = 1600,
        int $quality = 82,
    ): array {
        if (! $this->canOptimize($file)) {
            return $this->storeOriginal($file, $directory);
        }

        $realPath = $file->getRealPath();

        if (! is_string($realPath)) {
            throw new RuntimeException('Unable to read uploaded image.');
        }

        try {
            $image = Image::decodePath($realPath);
            $format = $this->preferredOutputFormat($image);

            if ($format === null) {
                return $this->storeOriginal($file, $directory);
            }

            $encoded = $image
                ->scaleDown($maxWidth, $maxHeight)
                ->encodeUsingFileExtension($format, quality: $quality);
        } catch (Throwable) {
            return $this->storeOriginal($file, $directory);
        }

        $contents = $encoded->toString();
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$format;

        Storage::disk('public')->put($path, $contents);

        return [
            'path' => $path,
            'size' => strlen($contents),
            'contentType' => self::OPTIMIZED_CONTENT_TYPES[$format],
        ];
    }

    public function canOptimize(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), self::OPTIMIZABLE_MIME_TYPES, true);
    }

    /**
     * @return array{path: string, size: int, contentType: string}
     */
    private function storeOriginal(UploadedFile $file, string $directory): array
    {
        $path = $file->store($directory, 'public');

        if (! is_string($path)) {
            throw new RuntimeException('Unable to store uploaded file.');
        }

        return [
            'path' => $path,
            'size' => $file->getSize(),
            'contentType' => $file->getMimeType() ?: 'application/octet-stream',
        ];
    }

    private function preferredOutputFormat(ImageInterface $image): ?string
    {
        foreach (array_keys(self::OPTIMIZED_CONTENT_TYPES) as $format) {
            if ($image->driver()->supports($format)) {
                return $format;
            }
        }

        return null;
    }
}
