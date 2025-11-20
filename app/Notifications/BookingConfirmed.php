<?php

namespace App\Notifications;   // ← make sure this is the correct namespace

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;   // ← THIS is the correct import

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;
public $booking;
    /**
     * Get the mail representation of the notification.
     */
    public function via($notifiable): array
    {
        return ['mail'];   // you can add 'database', 'broadcast', etc.
    }

    public function __construct(Booking $booking)
{
    $this->booking = $booking;
}

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject('Booking Confirmed #' . $this->booking->id)
        ->greeting('Hello ' . $notifiable->name . '!')
        ->line("Your booking for **{$this->booking->ticket->event->name}** is confirmed!")
        ->line("Quantity: {$this->booking->quantity}")
        ->action('View Booking', url('/bookings/' . $this->booking->id))
        ->line('Thank you for booking with us!');
    }
}