<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $events = Event::all()->pluck('id');

        Ticket::factory()->count(15)->create([
            'event_id' => $events->random(),
        ]);
    }
}
