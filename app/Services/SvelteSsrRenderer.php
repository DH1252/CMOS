<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use JsonException;
use Throwable;

class SvelteSsrRenderer
{
    public function __construct(private string $entryPoint = 'bootstrap/ssr/ssr.js') {}

    /**
     * @param  array<string, mixed>  $props
     * @return array{html: string, head: string, rendered: bool}
     */
    public function render(string $component, array $props = []): array
    {
        if (is_file(public_path('hot'))) {
            return $this->emptyResult();
        }

        $entryPoint = base_path($this->entryPoint);

        if (! is_file($entryPoint)) {
            return $this->emptyResult();
        }

        try {
            $payload = json_encode([
                'component' => $component,
                'props' => $props,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $result = Process::timeout(10)
                ->input($payload)
                ->run(sprintf('node "%s"', $entryPoint));
        } catch (Throwable $exception) {
            Log::warning('Svelte SSR process failed before completion.', [
                'component' => $component,
                'entry_point' => $entryPoint,
                'error' => $exception->getMessage(),
            ]);

            return $this->emptyResult();
        }

        if (! $result->successful()) {
            Log::warning('Svelte SSR process returned a non-zero exit code.', [
                'component' => $component,
                'entry_point' => $entryPoint,
                'error' => trim($result->errorOutput()),
            ]);

            return $this->emptyResult();
        }

        try {
            $decoded = json_decode($result->output(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Log::warning('Svelte SSR process returned invalid JSON.', [
                'component' => $component,
                'entry_point' => $entryPoint,
                'error' => $exception->getMessage(),
            ]);

            return $this->emptyResult();
        }

        $html = is_string($decoded['body'] ?? null) ? $decoded['body'] : '';
        $head = is_string($decoded['head'] ?? null) ? $decoded['head'] : '';

        return [
            'html' => $html,
            'head' => $head,
            'rendered' => $html !== '',
        ];
    }

    /**
     * @return array{html: string, head: string, rendered: bool}
     */
    private function emptyResult(): array
    {
        return [
            'html' => '',
            'head' => '',
            'rendered' => false,
        ];
    }
}
