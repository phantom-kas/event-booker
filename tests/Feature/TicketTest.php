<?php

namespace Tests\Feature;
use PHPUnit\Framework\Attributes\Test;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function organizer_can_create_ticket_for_their_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $event = Event::factory()->create(['created_by' => $organizer->id]);

        $payload = [
            'type' => 'VIP',
            'price' => 50,
            'quantity' => 10,
        ];

        $response = $this->postJson("/api/events/{$event->id}/tickets", $payload);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Ticket created successfully.']);

        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->id,
            'type' => 'VIP',
            'price' => 50,
            'quantity' => 10,
        ]);
    }

    #[Test]
    public function cannot_create_ticket_for_other_organizers_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $otherOrganizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $event = Event::factory()->create(['created_by' => $otherOrganizer->id]);

        $payload = ['type' => 'VIP', 'price' => 50, 'quantity' => 10];

        $response = $this->postJson("/api/events/{$event->id}/tickets", $payload);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Forbidden. You can only add tickets to your own events.']);
    }

    #[Test]
    public function cannot_create_ticket_for_non_existent_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $payload = ['type' => 'VIP', 'price' => 50, 'quantity' => 10];

        $response = $this->postJson("/api/events/999/tickets", $payload);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Event not found.']);
    }

    #[Test]
    public function organizer_can_update_their_ticket()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $ticket = Ticket::factory()->for($event)->create(['type' => 'Standard', 'price' => 30, 'quantity' => 20]);

        $payload = ['type' => 'VIP', 'price' => 50];

        $response = $this->putJson("/api/tickets/{$ticket->id}", $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'type' => 'VIP',
            'price' => 50,
        ]);
    }

    #[Test]
    public function cannot_update_ticket_of_other_organizers_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $otherOrganizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $event = Event::factory()->create(['created_by' => $otherOrganizer->id]);
        $ticket = Ticket::factory()->for($event)->create();

        $response = $this->putJson("/api/tickets/{$ticket->id}", ['price' => 50]);

        $response->assertStatus(403)
                 ->assertJson(['success' => false, 'message' => 'You are not authorized to update this ticket.']);
    }

    #[Test]
    public function organizer_can_delete_their_ticket()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $ticket = Ticket::factory()->for($event)->create();

        $response = $this->deleteJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Ticket deleted successfully.']);

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    #[Test]
    public function cannot_delete_ticket_of_other_organizers_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $otherOrganizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $event = Event::factory()->create(['created_by' => $otherOrganizer->id]);
        $ticket = Ticket::factory()->for($event)->create();

        $response = $this->deleteJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(403)
                 ->assertJson(['success' => false, 'message' => 'You are not authorized to delete this ticket.']);
    }

    #[Test]
    public function cannot_delete_non_existent_ticket()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $response = $this->deleteJson("/api/tickets/999");

        $response->assertStatus(404)
                 ->assertJson(['success' => false, 'message' => 'Ticket not found']);
    }
}
