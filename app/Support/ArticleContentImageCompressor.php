<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;

class ArticleContentImageCompressor
{
    private const MAX_WIDTH = 1600;

    private const MAX_HEIGHT = 1600;

    private const WEBP_QUALITY = 82;

    /**
     * @var array<int, string>
     */
    private const COMPRESSIBLE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function canCompress(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), self::COMPRESSIBLE_MIME_TYPES, true);
    }

    /**
     * @return array{path: string, size: int, contentType: string}
     */
    public function store(UploadedFile $file, string $directory = 'information-boards/attachments'): array
    {
        if (! $this->canCompress($file)) {
            return $this->storeOriginal($file, $directory);
        }

        $realPath = $file->getRealPath();

        if (! is_string($realPath)) {
            throw new RuntimeException('Unable to read uploaded image.');
        }

        $image = Image::decodePath($realPath);

        if (! $image->driver()->supports('webp')) {
            return $this->storeOriginal($file, $directory);
        }

        $encoded = $image
            ->scaleDown(self::MAX_WIDTH, self::MAX_HEIGHT)
            ->encode(new WebpEncoder(quality: self::WEBP_QUALITY));

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        $contents = $encoded->toString();

        Storage::disk('public')->put($path, $contents);

        return [
            'path' => $path,
            'size' => strlen($contents),
            'contentType' => 'image/webp',
        ];
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
}
