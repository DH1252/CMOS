<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('competitions:fetch', function () {
    $this->info("Starting competition data fetch...\n");

    $result = \Illuminate\Support\Facades\Process::path(base_path())
        ->run('node scripts/fetch-competitions.mjs');

    if ($result->successful()) {
        $this->info($result->output());
        $this->info('Competition data successfully compiled!');
    } else {
        $this->error($result->errorOutput());
        $this->error('Failed to compile competition data.');
    }
})->purpose('Fetch and compile latest competitions from Google Sheets');

\Illuminate\Support\Facades\Schedule::command('competitions:fetch')->dailyAt('01:00');
