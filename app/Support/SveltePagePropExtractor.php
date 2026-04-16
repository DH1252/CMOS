<?php

namespace App\Support;

use RuntimeException;

class SveltePagePropExtractor
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function extract(string $view, string $scriptId, array $data = []): array
    {
        $viewFactory = app('view');
        $payload = null;

        $viewData = [
            ...$data,
            '__svelteExtractingProps' => true,
        ];

        $sections = $viewFactory->make($view, $viewData)->renderSections();
        $contentHtml = trim((string) ($sections['content'] ?? ''));

        if ($contentHtml !== '') {
            $payload = $this->extractPayloadFromHtml($contentHtml, $scriptId);
        }

        if ($payload === null) {
            $fullHtml = $viewFactory->make($view, $viewData)->render();
            $payload = $this->extractPayloadFromHtml($fullHtml, $scriptId);
        }

        if ($payload === null) {
            throw new RuntimeException("Unable to locate JSON props script [{$scriptId}] in view [{$view}].");
        }

        $payload = trim($payload);

        if ($payload === '') {
            return [];
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Unable to decode JSON props for script [{$scriptId}] in view [{$view}].");
        }

        return $decoded;
    }

    private function extractPayloadFromHtml(string $html, string $scriptId): ?string
    {
        $pattern = sprintf(
            '/<script[^>]*id=["\']%s["\'][^>]*>(.*?)<\/script>/s',
            preg_quote($scriptId, '/')
        );

        if (! preg_match($pattern, $html, $matches)) {
            return null;
        }

        return $matches[1] ?? null;
    }
}
