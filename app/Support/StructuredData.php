<?php

namespace App\Support;

class StructuredData
{
    /**
     * @param  array<int, array<string, mixed>>  $nodes
     */
    public static function graph(array $nodes): array
    {
        return self::withoutEmptyValues([
            '@context' => 'https://schema.org',
            '@graph' => $nodes,
        ]);
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     */
    public static function breadcrumb(array $items, string $id): array
    {
        return [
            '@type' => 'BreadcrumbList',
            '@id' => $id,
            'itemListElement' => collect($items)->map(fn (array $item, int $index): array => self::withoutEmptyValues([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ]))->values()->all(),
        ];
    }

    public static function organization(string $name, string $url, string $logoUrl, ?string $sameAs = null): array
    {
        return self::withoutEmptyValues([
            '@type' => 'Organization',
            '@id' => $url.'#organization',
            'name' => $name,
            'url' => $url,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $logoUrl,
            ],
            'sameAs' => $sameAs ? [$sameAs] : null,
        ]);
    }

    public static function website(string $name, string $url, string $searchUrl, string $organizationId): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => $url.'#website',
            'name' => $name,
            'url' => $url,
            'publisher' => ['@id' => $organizationId],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $searchUrl.'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $articles
     */
    public static function itemList(array $articles, string $id): array
    {
        return [
            '@type' => 'ItemList',
            '@id' => $id,
            'itemListElement' => collect($articles)->map(fn (array $article, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $article['name'],
                'url' => $article['url'],
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function page(array $properties): array
    {
        return self::withoutEmptyValues(array_merge([
            '@type' => 'WebPage',
        ], $properties));
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function collectionPage(array $properties): array
    {
        return self::withoutEmptyValues(array_merge([
            '@type' => 'CollectionPage',
        ], $properties));
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function article(array $properties): array
    {
        return self::withoutEmptyValues(array_merge([
            '@type' => 'Article',
        ], $properties));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function encode(array $data): string
    {
        return json_encode(
            self::withoutEmptyValues($data),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
        );
    }

    /**
     * @param  array<string|int, mixed>  $data
     * @return array<string|int, mixed>
     */
    private static function withoutEmptyValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = self::withoutEmptyValues($value);
            }

            if ($value === null || $value === [] || $value === '') {
                unset($data[$key]);

                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }
}
