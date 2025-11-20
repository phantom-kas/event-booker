<?php

namespace Tests\Feature;
use PHPUnit\Framework\Attributes\Test;

use App\Models\User;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function customer_can_view_their_bookings()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $ticket = Ticket::factory()->for(Event::factory())->create();
        Booking::factory()->for($ticket)->create(['user_id' => $customer->id]);

        $response = $this->getJson('/api/bookings');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    #[Test]
    public function customer_can_book_a_ticket()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $ticket = Ticket::factory()->for(Event::factory())->create(['quantity' => 10]);
                
        $payload = ['quantity' => 2];

        $response = $this->postJson("/api/tickets/{$ticket->id}/bookings", $payload);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('bookings', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'quantity' => 2
        ]);
    }

    #[Test]
    public function booking_fails_if_ticket_not_found()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $response = $this->postJson('/api/tickets/999/bookings', ['quantity' => 1]);

        $response->assertStatus(404)
                 ->assertJson(['success' => false, 'message' => 'Ticket not found.']);
    }

    #[Test]
    public function booking_fails_if_quantity_exceeds_ticket()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $ticket = Ticket::factory()->for(Event::factory())->create(['quantity' => 5]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/bookings", ['quantity' => 10]);

        $response->assertStatus(422); // Laravel validation fails
    }

    #[Test]
    public function cannot_overbook_existing_bookings()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $ticket = Ticket::factory()->for(Event::factory())->create(['quantity' => 5]);

        Booking::factory()->for($ticket)->create(['quantity' => 3]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/bookings", ['quantity' => 3]);

        $response->assertStatus(400)
                 ->assertJson(['success' => false, 'message' => 'Not enough tickets available.']);
    }

    #[Test]
    public function customer_can_cancel_their_booking()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'status' => 'pending'
        ]);

        $response = $this->putJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Booking cancelled successfully.']);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled'
        ]);
    }

    #[Test]
    public function cannot_cancel_booking_twice()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'status' => 'cancelled'
        ]);

        $response = $this->putJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(400)
                 ->assertJson(['success' => false, 'message' => 'Booking is already cancelled.']);
    }

    #[Test]
    public function cannot_cancel_others_booking()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $otherUser = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $booking = Booking::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'pending'
        ]);

        $response = $this->putJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(404)
                 ->assertJson(['success' => false, 'message' => 'Booking not found.']);
    }
}
