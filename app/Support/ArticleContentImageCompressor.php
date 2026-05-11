<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class ArticleContentImageCompressor
{
    private const MAX_WIDTH = 1600;

    private const MAX_HEIGHT = 1600;

    private const WEBP_QUALITY = 82;

    public function __construct(private readonly UploadedImageOptimizer $optimizer) {}

    public function canCompress(UploadedFile $file): bool
    {
        return $this->optimizer->canOptimize($file);
    }

    /**
     * @return array{path: string, size: int, contentType: string}
     */
    public function store(UploadedFile $file, string $directory = 'information-boards/attachments'): array
    {
        return $this->optimizer->store(
            $file,
            $directory,
            self::MAX_WIDTH,
            self::MAX_HEIGHT,
            self::WEBP_QUALITY,
        );
    }
}
