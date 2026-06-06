<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'poster_image',
        'location',
        'starts_at',
        'ends_at',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event): void {
            if (blank($event->slug)) {
                $event->slug = static::generateUniqueSlug($event->title);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now())
            ->orderBy('starts_at');
    }

    public function getPosterImageUrlAttribute(): ?string
    {
        if (! $this->poster_image) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->poster_image)) {
            return null;
        }

        return asset('storage/'.$this->poster_image);
    }

    /**
     * @return array{original: string|null, webp: string|null, avif: string|null, width: int|null, height: int|null}|null
     */
    public function getPosterImageOptimizedAttribute(): ?array
    {
        $original = $this->poster_image_url;

        if ($original === null) {
            return null;
        }

        $optimizeUrl = route('images.optimize', ['path' => $this->poster_image]);
        $dimensions = @getimagesize(Storage::disk('public')->path($this->poster_image)) ?: [null, null];

        return [
            'original' => $original,
            'avif' => $optimizeUrl.'?f=avif',
            'webp' => $optimizeUrl.'?f=webp',
            'width' => is_int($dimensions[0] ?? null) ? $dimensions[0] : null,
            'height' => is_int($dimensions[1] ?? null) ? $dimensions[1] : null,
        ];
    }

    public function getDescriptionOptimizedAttribute(): string
    {
        if (! is_string($this->description) || $this->description === '') {
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

                if (! str_contains($lowerTag, ' style=')) {
                    $tag = preg_replace('/\s*\/?>$/', ' style="max-width: 100%; height: auto; object-fit: contain;">', $tag, 1) ?? $tag;
                }

                return $tag;
            },
            $this->description,
        ) ?? $this->description;
    }

    public function getStartsAtLocalAttribute(): ?Carbon
    {
        return $this->starts_at?->copy()->setTimezone(config('app.client_timezone', 'Asia/Jakarta'));
    }

    public function getEndsAtLocalAttribute(): ?Carbon
    {
        return $this->ends_at?->copy()->setTimezone(config('app.client_timezone', 'Asia/Jakarta'));
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

        return Str::limit(strip_tags($this->description), 160);
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'acara';
        }

        $slug = $base;
        $counter = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
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

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'avif', 'webp'], true)) {
            return null;
        }

        return route('images.optimize', [
            'path' => $storagePath,
            'f' => 'avif',
            'w' => 1280,
        ], false);
    }
}
