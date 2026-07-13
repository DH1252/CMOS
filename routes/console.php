<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('competitions:fetch', function () {
    $this->info("Starting competition data fetch...\n");

    // Resolve Bun/Node executable path
    $runCmd = 'node';
    if (file_exists('/home/dharon/.bun/bin/bun')) {
        $runCmd = '/home/dharon/.bun/bin/bun';
    } elseif (file_exists('/home/dharon/.nvm/versions/node/v24.14.0/bin/node')) {
        $runCmd = '/home/dharon/.nvm/versions/node/v24.14.0/bin/node';
    }

    $result = \Illuminate\Support\Facades\Process::path(base_path())
        ->run("$runCmd scripts/fetch-competitions.mjs");

    if ($result->successful()) {
        $this->info($result->output());
        $this->info('Competition data successfully compiled!');
    } else {
        $this->error($result->errorOutput());
        $this->error('Failed to compile competition data.');
    }
})->purpose('Fetch and compile latest competitions from Google Sheets');

\Illuminate\Support\Facades\Schedule::command('competitions:fetch')->dailyAt('01:00');
