<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim($this->faker->sentence(4), '.');
        $startsAt = Carbon::instance($this->faker->dateTimeBetween('+1 week', '+3 months'));

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 100000),
            'description' => '<p>'.$this->faker->paragraph(5).'</p>',
            'poster_image' => null,
            'location' => $this->faker->randomElement([
                'Gedung Teknik Komputer ITS',
                'Auditorium ITS',
                'Daring (Zoom)',
                'Lapangan Teknik Komputer',
            ]),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->addHours(3),
            'status' => 'published',
            'published_at' => now(),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function past(): static
    {
        return $this->state(function (): array {
            $startsAt = Carbon::instance($this->faker->dateTimeBetween('-3 months', '-1 week'));

            return [
                'starts_at' => $startsAt,
                'ends_at' => (clone $startsAt)->addHours(3),
            ];
        });
    }
}
