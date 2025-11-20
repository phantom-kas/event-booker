<?php

namespace Tests\Feature;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function organizer_can_create_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $payload = [
            'title' => 'Sample Event',
            'description' => 'Test description',
            'location' => 'Accra',
            'start_date' => now()->addDays(1)->toDateTimeString(),
            'end_date' => now()->addDays(2)->toDateTimeString(),
            'capacity' => 100,
            'price' => 50,
        ];

        $response = $this->postJson('/api/events', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Event created successfully.',
                 ]);

        $this->assertDatabaseHas('events', ['title' => 'Sample Event']);
    }

    #[Test]
    public function customer_cannot_create_event()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $response = $this->postJson('/api/events', [
            'title' => 'Unauthorized Event'
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function event_creation_requires_validation()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $response = $this->postJson('/api/events', []);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors']);
    }

    #[Test]
    public function anyone_can_view_event_index()
    {
        Event::factory()->count(3)->create();

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    #[Test]
    public function anyone_can_view_single_event()
    {
        $event = Event::factory()->create();

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => ['id' => $event->id]
                 ]);
    }

    #[Test]
    public function viewing_nonexistent_event_returns_404()
    {
        $response = $this->getJson('/api/events/999');

        $response->assertStatus(404)
                 ->assertJson(['success' => false, 'message' => 'Event not found']);
    }

    #[Test]
    public function organizer_can_update_their_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $event = Event::factory()->create(['created_by' => $organizer->id]);

        $payload = ['title' => 'Updated Event Title'];

        $response = $this->putJson("/api/events/{$event->id}", $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Event updated successfully.']);

        $this->assertDatabaseHas('events', ['id' => $event->id, 'title' => 'Updated Event Title']);
    }

    #[Test]
    public function organizer_cannot_update_others_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $otherEvent = Event::factory()->create();

        $response = $this->putJson("/api/events/{$otherEvent->id}", ['title' => 'Hacked']);

        $response->assertStatus(403);
    }

    #[Test]
    public function organizer_can_delete_their_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $event = Event::factory()->create(['created_by' => $organizer->id]);

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Event deleted successfully']);

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    #[Test]
    public function organizer_cannot_delete_others_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $event = Event::factory()->create();

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function deleting_nonexistent_event_returns_404()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer);

        $response = $this->deleteJson('/api/events/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Event not found']);
    }
}
