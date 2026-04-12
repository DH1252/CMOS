<?php

namespace Tests\Unit;

use App\Services\SvelteSsrRenderer;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class SvelteSsrRendererTest extends TestCase
{
    public function test_render_returns_empty_result_when_ssr_bundle_is_missing(): void
    {
        $renderer = new SvelteSsrRenderer('bootstrap/ssr/missing-entry.js');

        $this->assertSame([
            'html' => '',
            'head' => '',
            'rendered' => false,
        ], $renderer->render('publicApp', ['page' => 'landing']));
    }

    public function test_render_returns_empty_result_while_vite_dev_server_is_running(): void
    {
        $entryPoint = 'storage/framework/testing-ssr-entry.js';
        $entryPointPath = base_path($entryPoint);
        $hotFilePath = public_path('hot');

        file_put_contents($entryPointPath, '// test stub');
        file_put_contents($hotFilePath, 'http://127.0.0.1:5173');

        Process::fake([
            'node *' => Process::result(output: json_encode([
                'body' => '<main>Unexpected SSR content</main>',
                'head' => '<meta name="ssr" content="unexpected">',
            ], JSON_THROW_ON_ERROR)),
        ]);

        try {
            $renderer = new SvelteSsrRenderer($entryPoint);

            $this->assertSame([
                'html' => '',
                'head' => '',
                'rendered' => false,
            ], $renderer->render('publicApp', ['page' => 'landing']));
        } finally {
            @unlink($entryPointPath);
            @unlink($hotFilePath);
        }
    }

    public function test_render_returns_markup_when_ssr_process_succeeds(): void
    {
        $entryPoint = 'storage/framework/testing-ssr-entry.js';
        $entryPointPath = base_path($entryPoint);

        file_put_contents($entryPointPath, '// test stub');

        Process::fake([
            'node *' => Process::result(output: json_encode([
                'body' => '<main data-ssr="public-app">SSR content</main>',
                'head' => '<meta name="ssr" content="enabled">',
            ], JSON_THROW_ON_ERROR)),
        ]);

        try {
            $renderer = new SvelteSsrRenderer($entryPoint);

            $this->assertSame([
                'html' => '<main data-ssr="public-app">SSR content</main>',
                'head' => '<meta name="ssr" content="enabled">',
                'rendered' => true,
            ], $renderer->render('publicApp', ['page' => 'landing']));
        } finally {
            @unlink($entryPointPath);
        }
    }
}
