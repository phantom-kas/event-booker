<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $users = User::all()->pluck('id');
        $tickets = Ticket::all()->pluck('id');
        Booking::factory()->count(20)->create([
            'user_id' => $users->random(),
            'ticket_id' => $tickets->random(),
        ]);
    }
}
