<?php

namespace App\Console\Commands;

use App\Support\OptimizedImageCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class WarmOptimizedImages extends Command
{
    protected $signature = 'images:warm-optimized
        {path? : Public disk directory or image path to warm}
        {--width=* : Widths to pre-generate for responsive images}
        {--format=* : Formats to pre-generate in priority order}
        {--quality=85 : Encoded image quality from 1 to 100}
        {--limit=0 : Maximum number of source images to process}';

    protected $description = 'Warm cached optimized image variants on the public disk';

    public function handle(OptimizedImageCache $imageCache): int
    {
        $disk = Storage::disk('public');
        $root = trim((string) ($this->argument('path') ?? ''), '/');
        $paths = $root !== '' && $disk->exists($root) && $imageCache->canOptimizePath($root)
            ? [$root]
            : $disk->allFiles($root);

        $paths = array_values(array_filter($paths, fn (string $path): bool => $imageCache->canOptimizePath($path)));
        $limit = $this->resolveLimit($this->option('limit'));

        if ($limit > 0) {
            $paths = array_slice($paths, 0, $limit);
        }

        $formats = $this->resolveFormats($this->option('format'));
        $widths = $this->resolveWidths($this->option('width'));
        $quality = $this->resolveQuality($this->option('quality'));
        $created = 0;
        $cached = 0;
        $failed = 0;

        foreach ($paths as $path) {
            foreach ($formats as $format) {
                foreach ($widths as $width) {
                    $variant = $imageCache->optimize($path, $format, $width, null, $quality);

                    if ($variant === null) {
                        $failed++;

                        continue;
                    }

                    $variant['cached'] ? $cached++ : $created++;
                }
            }
        }

        $this->info("Optimized image cache warmed: {$created} created, {$cached} already cached, {$failed} failed.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function resolveFormats(mixed $value): array
    {
        $formats = is_array($value) && $value !== [] ? $value : OptimizedImageCache::SUPPORTED_FORMATS;

        return array_values(array_filter(
            array_map(fn (mixed $format): string => strtolower((string) $format), $formats),
            fn (string $format): bool => in_array($format, OptimizedImageCache::SUPPORTED_FORMATS, true),
        )) ?: OptimizedImageCache::SUPPORTED_FORMATS;
    }

    /**
     * @return array<int, int>
     */
    private function resolveWidths(mixed $value): array
    {
        $widths = is_array($value) && $value !== [] ? $value : [320, 480, 640, 960, 1280, 1600, 1920];

        return array_values(array_unique(array_filter(
            array_map(fn (mixed $width): int => (int) $width, $widths),
            fn (int $width): bool => $width > 0 && $width <= 4096,
        ))) ?: [1600];
    }

    private function resolveQuality(mixed $value): int
    {
        $quality = filter_var($value, FILTER_VALIDATE_INT);

        return $quality === false || $quality < 1 || $quality > 100 ? 85 : $quality;
    }

    private function resolveLimit(mixed $value): int
    {
        $limit = filter_var($value, FILTER_VALIDATE_INT);

        return $limit === false || $limit < 0 ? 0 : $limit;
    }
}
