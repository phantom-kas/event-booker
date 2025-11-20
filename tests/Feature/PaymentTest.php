<?php

namespace Tests\Feature;
use PHPUnit\Framework\Attributes\Test;

use App\Models\User;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function customer_can_pay_for_their_booking()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create(['price' => 100]);
        $booking = Booking::factory()->for($ticket)->create([
            'user_id' => $customer->id,
            'quantity' => 2,
            'status' => 'pending'
        ]);

        $response = $this->postJson("/api/bookings/{$booking->id}/payment");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Payment successful'
                 ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => 200,
            'status' => 'success'
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed'
        ]);
    }

    #[Test]
    public function cannot_pay_for_non_existent_booking()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $response = $this->postJson('/api/bookings/999/payment');

        $response->assertStatus(404)
                 ->assertJson(['success' => false, 'message' => 'Booking not found.']);
    }

    #[Test]
    public function cannot_pay_for_already_paid_booking()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'status' => 'confirmed'
        ]);

        Payment::factory()->for($booking)->create([
            'amount' => 100,
            'status' => 'success'
        ]);

        $response = $this->postJson("/api/bookings/{$booking->id}/payment");

        $response->assertStatus(400)
                 ->assertJson(['success' => false, 'message' => 'Booking is already paid.']);
    }

    #[Test]
    public function cannot_pay_for_cancelled_booking()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'status' => 'cancelled'
        ]);

        $response = $this->postJson("/api/bookings/{$booking->id}/payment");

        $response->assertStatus(400)
                 ->assertJson(['success' => false, 'message' => 'Booking is cancelled.']);
    }

  

    #[Test]
    public function cannot_view_non_existent_payment()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $response = $this->getJson('/api/payments/999');

        $response->assertStatus(404)
                 ->assertJson(['success' => false, 'message' => 'Payment not found.']);
    }

   
}
