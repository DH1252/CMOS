<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    /**
     * Public department pages (solar system + detail) that an internal
     * department can be mapped to via its slug.
     *
     * @var array<string, string>
     */
    public const PUBLIC_PAGE_OPTIONS = [
        'bph' => 'Badan Pengurus Harian (BPH)',
        'personalia' => 'Biro Personalia',
        'dagri' => 'Dalam Negeri (DAGRI)',
        'hublu' => 'Hubungan Luar (HUBLU)',
        'psdm' => 'Pengembangan Sumber Daya Mahasiswa (PSDM)',
        'kesma' => 'Kesejahteraan Mahasiswa (KESMA)',
        'risprof' => 'Riset dan Keprofesian (RISPROF)',
        'medfo' => 'Media dan Informasi (MEDFO)',
        'kwu' => 'Kewirausahaan (KWU)',
        'kaderisasi' => 'Kaderisasi (TUK)',
    ];

    /**
     * Lowercase keywords used to guess which public page a department name
     * maps to. Abbreviations/short tokens score higher than phrases.
     * Shared with the form for live client-side suggestion.
     *
     * @var array<string, array<int, string>>
     */
    public const SLUG_KEYWORDS = [
        'bph' => ['bph', 'badan pengurus harian', 'badan pengurus inti', 'bpi'],
        'personalia' => ['personalia'],
        'dagri' => ['dagri', 'dalam negeri'],
        'hublu' => ['hublu', 'hubungan luar', 'humas'],
        'psdm' => ['psdm', 'pengembangan sumber daya', 'sumber daya mahasiswa'],
        'kesma' => ['kesma', 'kesejahteraan'],
        'risprof' => ['risprof', 'riset dan keprofesian', 'ristek', 'keprofesian', 'riset'],
        'medfo' => ['medfo', 'medinfo', 'media dan informasi', 'media informasi'],
        'kwu' => ['kwu', 'kewirausahaan'],
        'kaderisasi' => ['kaderisasi', 'tuk', 'kader'],
    ];

    /**
     * Guess the public page slug for a department name based on keyword
     * matching. Abbreviations (<=6 chars) outweigh descriptive phrases.
     */
    public static function suggestSlug(?string $name): ?string
    {
        $haystack = mb_strtolower((string) $name);
        if ($haystack === '') {
            return null;
        }

        $best = null;
        $bestScore = 0;

        foreach (self::SLUG_KEYWORDS as $slug => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    $score += mb_strlen($keyword) <= 6 ? 10 : 4;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $slug;
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    protected $fillable = ['name', 'slug', 'description', 'cabinet_id', 'status', 'staff_graphics', 'staff_order', 'overlays_disabled'];

    protected $casts = [
        'status' => 'string',
        'staff_graphics' => 'array',
        'staff_order' => 'array',
        'overlays_disabled' => 'boolean',
    ];

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(Timeline::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getHeadAttribute()
    {
        return $this->users()->whereHas('role', function ($q) {
            $q->where('name', 'kabinet');
        })->first();
    }
}
