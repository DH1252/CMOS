<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Setting;
use App\Support\StructuredData;
use App\Support\ThemePalette;
use DateTimeInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class PublicEventController extends Controller
{
    public function index()
    {
        $events = Event::query()
            ->select(['id', 'user_id', 'title', 'slug', 'description', 'poster_image', 'location', 'starts_at', 'ends_at', 'published_at'])
            ->with(['user:id,name'])
            ->published()
            ->upcoming()
            ->paginate(9)
            ->withQueryString();

        return \Inertia\Inertia::render('PublicApp', $this->indexPayload($events));
    }

    public function show(Event $event)
    {
        abort_unless($event->status === 'published' && (! $event->published_at || $event->published_at->lte(now())), 404);

        $event->load(['user:id,name']);

        $upcomingEvents = Event::published()
            ->upcoming()
            ->where('id', '!=', $event->id)
            ->select(['id', 'title', 'slug', 'starts_at'])
            ->take(5)
            ->get();

        return \Inertia\Inertia::render('PublicApp', $this->showPayload($event, $upcomingEvents));
    }

    /**
     * @param  LengthAwarePaginator<int, Event>  $events
     * @return array<string, mixed>
     */
    private function indexPayload(LengthAwarePaginator $events): array
    {
        $settings = $this->publicSettings();
        $homeUrl = route('home');
        $acaraUrl = route('acara.index');
        $infoUrl = route('informasi.index');
        $logoUrl = asset('images/logokabinet.avif');
        $organizationId = $homeUrl.'#organization';
        $websiteId = $homeUrl.'#website';
        $itemListId = $acaraUrl.'#item-list';

        $eventItems = $events->getCollection()->values();
        $schemaItems = $eventItems->map(fn (Event $event): array => [
            'name' => $event->title,
            'url' => route('acara.show', $event->slug),
        ])->all();

        $jsonLdNodes = [
            StructuredData::organization($settings['organizationName'], $homeUrl, $logoUrl),
            StructuredData::website($settings['organizationName'], $homeUrl, $infoUrl, $organizationId),
            StructuredData::collectionPage([
                '@id' => $acaraUrl.'#webpage',
                'url' => $acaraUrl,
                'name' => 'Acara Mendatang - '.$settings['organizationName'],
                'description' => 'Daftar acara dan kegiatan mendatang HIMATEKKOM ITS.',
                'isPartOf' => ['@id' => $websiteId],
                'publisher' => ['@id' => $organizationId],
                'mainEntity' => $schemaItems === [] ? null : ['@id' => $itemListId],
                'inLanguage' => 'id-ID',
            ]),
        ];

        if ($schemaItems !== []) {
            $jsonLdNodes[] = StructuredData::itemList($schemaItems, $itemListId);
        }

        return [
            'page' => 'acara-index',
            'appName' => $settings['appName'],
            'organizationName' => $settings['organizationName'],
            'themeColor' => $settings['themeColor'],
            'themeVariables' => $settings['themeVariables'],
            'themeCustomCss' => $settings['themeCustomCss'],
            'homeUrl' => $homeUrl,
            'loginUrl' => route('login'),
            'infoUrl' => $infoUrl,
            'acaraUrl' => $acaraUrl,
            'logoUrl' => $logoUrl,
            'seo' => $this->buildSeoPayload(
                title: 'Acara Mendatang - '.$settings['organizationName'],
                description: 'Daftar acara dan kegiatan mendatang HIMATEKKOM ITS.',
                canonical: $acaraUrl,
                image: $logoUrl,
                type: 'website',
                jsonLd: StructuredData::graph($jsonLdNodes),
            ),
            'acaraIndex' => [
                'title' => 'Acara Mendatang',
                'kicker' => 'Agenda Kabinet',
                'description' => 'Jadwal acara dan kegiatan terbaru HIMATEKKOM ITS yang akan datang.',
                'events' => $eventItems->map(fn (Event $event) => [
                    'title' => $event->title,
                    'location' => $event->location,
                    'poster' => $event->poster_image_optimized,
                    'dateLabel' => $this->formatPublicDate($event->startsAtLocal, includeTime: true),
                    'href' => route('acara.show', $event->slug),
                ])->values(),
                'pagination' => [
                    'currentPage' => $events->currentPage(),
                    'lastPage' => $events->lastPage(),
                    'prevUrl' => $events->previousPageUrl(),
                    'nextUrl' => $events->nextPageUrl(),
                    'from' => $events->firstItem() ?? 0,
                    'to' => $events->lastItem() ?? 0,
                    'total' => $events->total(),
                ],
                'emptyText' => 'Belum ada acara mendatang yang dipublikasikan.',
            ],
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Event>  $upcomingEvents
     * @return array<string, mixed>
     */
    private function showPayload(Event $event, $upcomingEvents): array
    {
        $settings = $this->publicSettings();
        $homeUrl = route('home');
        $acaraUrl = route('acara.index');
        $infoUrl = route('informasi.index');
        $canonicalUrl = route('acara.show', $event);
        $logoUrl = asset('images/logokabinet.avif');
        $poster = $event->poster_image_optimized;
        $posterUrl = $poster['avif'] ?? $poster['webp'] ?? $event->poster_image_url;
        $organizationId = $homeUrl.'#organization';
        $websiteId = $homeUrl.'#website';

        $eventNode = StructuredData::page([
            '@type' => 'Event',
            '@id' => $canonicalUrl.'#event',
            'name' => $event->seo_title,
            'description' => $event->seo_description,
            'url' => $canonicalUrl,
            'startDate' => optional($event->startsAtLocal)?->toIso8601String(),
            'endDate' => optional($event->endsAtLocal)?->toIso8601String(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'image' => $posterUrl ? [$posterUrl] : null,
            'location' => $event->location ? [
                '@type' => 'Place',
                'name' => $event->location,
            ] : null,
            'organizer' => ['@id' => $organizationId],
            'inLanguage' => 'id-ID',
        ]);

        $showJsonLdNodes = [
            StructuredData::organization($settings['organizationName'], $homeUrl, $logoUrl),
            StructuredData::website($settings['organizationName'], $homeUrl, $infoUrl, $organizationId),
            $eventNode,
            StructuredData::breadcrumb([
                ['name' => 'Beranda', 'url' => $homeUrl],
                ['name' => 'Acara', 'url' => $acaraUrl],
                ['name' => $event->title, 'url' => $canonicalUrl],
            ], $canonicalUrl.'#breadcrumb'),
        ];

        return [
            'page' => 'acara-show',
            'appName' => $settings['appName'],
            'organizationName' => $settings['organizationName'],
            'themeColor' => $settings['themeColor'],
            'themeVariables' => $settings['themeVariables'],
            'themeCustomCss' => $settings['themeCustomCss'],
            'homeUrl' => $homeUrl,
            'loginUrl' => route('login'),
            'infoUrl' => $infoUrl,
            'acaraUrl' => $acaraUrl,
            'logoUrl' => $logoUrl,
            'seo' => $this->buildSeoPayload(
                title: $event->seo_title.' - '.$settings['organizationName'],
                description: $event->seo_description,
                canonical: $canonicalUrl,
                image: $posterUrl ?? $logoUrl,
                type: 'article',
                jsonLd: StructuredData::graph($showJsonLdNodes),
            ),
            'acaraShow' => [
                'event' => [
                    'title' => $event->title,
                    'seoTitle' => $event->seo_title,
                    'location' => $event->location,
                    'dateLabel' => $this->formatPublicDate($event->startsAtLocal, includeTime: true),
                    'endDateLabel' => $this->formatPublicDate($event->endsAtLocal, includeTime: true),
                    'poster' => $event->poster_image_optimized,
                    'contentHtml' => $event->description_optimized,
                    'excerpt' => $event->seo_description,
                ],
                'upcomingEvents' => $upcomingEvents->map(fn (Event $item) => [
                    'title' => $item->title,
                    'dateLabel' => $this->formatPublicDate($item->startsAtLocal, includeTime: true),
                    'href' => route('acara.show', $item->slug),
                ])->values(),
            ],
        ];
    }

    /**
     * @return array{appName: string, organizationName: string, themeColor: string, themeVariables: array<string, string>, themeCustomCss: array{light: array<string, string>, dark: array<string, string>, shared: array<string, string>}}
     */
    private function publicSettings(): array
    {
        $settings = Setting::query()
            ->whereIn('key', array_merge(['app_name', 'organization_name', 'theme_color'], ThemePalette::settingKeys(), ThemePalette::cssVariableKeys()))
            ->pluck('value', 'key');
        $themePayload = ThemePalette::payloadFromSettings($settings->all());

        return [
            'appName' => (string) $settings->get('app_name', 'CMOS'),
            'organizationName' => (string) $settings->get('organization_name', 'HIMATEKKOM ITS'),
            'themeColor' => $themePayload['color'],
            'themeVariables' => $themePayload['variables'],
            'themeCustomCss' => $themePayload['customCss'],
        ];
    }

    /**
     * @param  array<string, mixed>  $jsonLd
     * @return array{title: string, description: string, canonical: string, image: string|null, type: string, jsonLd: string|null}
     */
    private function buildSeoPayload(string $title, string $description, string $canonical, ?string $image = null, string $type = 'website', array $jsonLd = []): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'image' => $image,
            'type' => $type,
            'jsonLd' => $jsonLd === [] ? null : StructuredData::encode($jsonLd),
        ];
    }

    private function formatPublicDate(?DateTimeInterface $date, bool $includeTime = false): ?string
    {
        if (! $date) {
            return null;
        }

        return Carbon::instance($date)
            ->locale('id')
            ->translatedFormat($includeTime ? 'd M Y H:i' : 'd M Y');
    }
}
