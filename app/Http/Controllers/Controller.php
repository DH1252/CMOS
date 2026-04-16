<?php

namespace App\Http\Controllers;

use App\Support\SveltePagePropExtractor;
use Inertia\Inertia;
use Inertia\Response;

abstract class Controller
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $viewData
     */
    protected function renderInertiaPage(
        string $component,
        array $props = [],
        ?string $pageTitle = null,
        ?string $pageMeta = null,
        ?string $view = null,
        ?string $scriptId = null,
        array $viewData = [],
    ): Response {
        if ($view && $scriptId) {
            $props = [
                ...app(SveltePagePropExtractor::class)->extract($view, $scriptId, $viewData),
                ...$props,
            ];
        }

        if ($pageTitle !== null) {
            $props['pageTitle'] = $pageTitle;
        }

        if ($pageMeta !== null) {
            $props['pageMeta'] = $pageMeta;
        }

        return Inertia::render($component, $props);
    }
}
