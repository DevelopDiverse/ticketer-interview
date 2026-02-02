<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $users = User::factory()
            ->count(5)
            ->create();

        $openEvent = Event::factory()
            ->create([
                'name' => 'Open Conference 2026',
                'capacity' => 500,
            ]);

        $tightEvent = Event::factory()
            ->create([
                'name' => 'Limited Workshop',
                'capacity' => 3,
            ]);

        Reservation::factory()
            ->count(2)
            ->forEvent($tightEvent)
            ->forUser($users->random())
            ->create();

        Event::factory()
            ->count(3)
            ->create()
            ->each(function (Event $event) use ($users) {
                Reservation::factory()
                    ->count(rand(0, 3))
                    ->forEvent($event)
                    ->forUser($users->random())
                    ->create();
            });
    }
}
