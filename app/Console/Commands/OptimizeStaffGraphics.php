<?php

namespace App\Console\Commands;

use App\Models\Department;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class OptimizeStaffGraphics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-staff-graphics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize existing staff graphics to WebP and scale them down.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting staff graphics optimization...');

        $departments = Department::all();
        $optimizedCount = 0;

        foreach ($departments as $department) {
            $graphics = $department->staff_graphics;
            if (! $graphics || ! is_array($graphics)) {
                continue;
            }

            $updated = false;
            foreach ($graphics as $key => $graphic) {
                if (! isset($graphic['image'])) {
                    continue;
                }

                $url = $graphic['image'];

                // Skip if already webp or avif
                if (Str::endsWith($url, ['.webp', '.avif'])) {
                    continue;
                }

                $this->info("Optimizing image: {$url}");

                // Extract path relative to storage/app/public
                $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));

                if (! Storage::disk('public')->exists($path)) {
                    $this->warn("File not found on disk: {$path}");

                    continue;
                }

                try {
                    $fileContent = Storage::disk('public')->get($path);
                    $image = Image::decode($fileContent);

                    $encoded = $image->encodeUsingFileExtension('webp', 80);
                    $newFilename = uniqid('staff_').'.webp';
                    $newPath = 'departments/graphics/'.$newFilename;

                    Storage::disk('public')->put($newPath, (string) $encoded);

                    // Update URL in JSON
                    $graphics[$key]['image'] = Storage::url($newPath);
                    $updated = true;
                    $optimizedCount++;

                    $this->info("Successfully optimized to: {$newPath}");

                    // Optionally delete old file
                    Storage::disk('public')->delete($path);
                } catch (\Exception $e) {
                    $this->error("Failed to optimize {$path}: ".$e->getMessage());
                }
            }

            if ($updated) {
                $department->staff_graphics = $graphics;
                $department->save();
                $this->info("Updated database for department: {$department->name}");
            }
        }

        $this->info("Finished! Optimized {$optimizedCount} images.");
    }
}
