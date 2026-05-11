<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InformationBoard extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (InformationBoard $article) {
            if (blank($article->slug)) {
                $article->slug = static::generateUniqueSlug($article->title);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(InformationCategory::class, 'information_board_category');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->cover_image)) {
            return null;
        }

        return asset('storage/'.$this->cover_image);
    }

    /**
     * @return array{original: string|null, webp: string|null, avif: string|null, width: int|null, height: int|null}|null
     */
    public function getCoverImageOptimizedAttribute(): ?array
    {
        $original = $this->cover_image_url;

        if ($original === null) {
            return null;
        }

        $optimizeUrl = route('images.optimize', ['path' => $this->cover_image]);
        $dimensions = @getimagesize(Storage::disk('public')->path($this->cover_image)) ?: [null, null];

        return [
            'original' => $original,
            'webp' => $optimizeUrl.'?f=webp',
            'avif' => null,
            'width' => is_int($dimensions[0] ?? null) ? $dimensions[0] : null,
            'height' => is_int($dimensions[1] ?? null) ? $dimensions[1] : null,
        ];
    }

    public function getContentOptimizedAttribute(): string
    {
        if (! is_string($this->content) || $this->content === '') {
            return '';
        }

        return preg_replace_callback(
            '#<img\b[^>]*\bsrc\s*=\s*(["\'])([^"\']+)\1[^>]*>#i',
            function (array $matches): string {
                $optimizedUrl = $this->optimizedStorageImageUrl($matches[2]);

                if ($optimizedUrl === null) {
                    return $matches[0];
                }

                $tag = preg_replace('#\bsrc\s*=\s*(["\']).*?\1#i', 'src="'.$optimizedUrl.'"', $matches[0], 1) ?? $matches[0];
                $lowerTag = strtolower($tag);

                if (! str_contains($lowerTag, ' loading=')) {
                    $tag = preg_replace('/\s*\/?>$/', ' loading="lazy">', $tag, 1) ?? $tag;
                }

                if (! str_contains($lowerTag, ' decoding=')) {
                    $tag = preg_replace('/\s*\/?>$/', ' decoding="async">', $tag, 1) ?? $tag;
                }

                return $tag;
            },
            $this->content,
        ) ?? $this->content;
    }

    public function getPublishedAtLocalAttribute(): ?Carbon
    {
        return $this->published_at?->copy()->setTimezone(config('app.client_timezone', 'Asia/Jakarta'));
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function getSeoDescriptionAttribute(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        return Str::limit(strip_tags($this->excerpt ?: $this->content), 160);
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'artikel';
        }

        $slug = $base;
        $counter = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function optimizedStorageImageUrl(string $source): ?string
    {
        $path = parse_url($source, PHP_URL_PATH);

        if (! is_string($path) || ! str_starts_with($path, '/storage/')) {
            return null;
        }

        $storagePath = rawurldecode(Str::after($path, '/storage/'));
        $extension = strtolower(pathinfo($storagePath, PATHINFO_EXTENSION));

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return null;
        }

        return route('images.optimize', [
            'path' => $storagePath,
            'f' => 'webp',
            'w' => 1280,
        ], false);
    }
}
