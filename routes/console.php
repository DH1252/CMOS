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

    // Resolve Spreadsheet ID from Settings
    $spreadsheetUrl = \App\Models\Setting::get('competition_spreadsheet_url', 'https://docs.google.com/spreadsheets/d/1rHMZoGB3RgzDVwRqW0QalCahShWGMdLTOQVO62x5EK4/edit');
    $spreadsheetId = '1rHMZoGB3RgzDVwRqW0QalCahShWGMdLTOQVO62x5EK4';
    if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $spreadsheetUrl, $matches)) {
        $spreadsheetId = $matches[1];
    }

    $result = \Illuminate\Support\Facades\Process::path(base_path())
        ->env([
            'COMPETITION_SPREADSHEET_ID' => $spreadsheetId,
        ])
        ->run("$runCmd scripts/fetch-competitions.mjs");

    if ($result->successful()) {
        $this->info($result->output());
        $this->info('Competition data successfully compiled!');
    } else {
        $this->error($result->errorOutput());
        $this->error('Failed to compile competition data.');
    }
})->purpose('Fetch and compile latest competitions from Google Sheets');

$time = '01:00';
try {
    if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
        $time = \App\Models\Setting::get('competition_schedule_time', '01:00');
    }
} catch (\Throwable $e) {
    // Fallback if database is unmigrated during app bootstrap (e.g. testing)
}

if (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
    $time = '01:00';
}
\Illuminate\Support\Facades\Schedule::command('competitions:fetch')->dailyAt($time);
