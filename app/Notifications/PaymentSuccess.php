<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // <-- add this
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Payment;

class PaymentSuccess extends Notification implements ShouldQueue // <-- implement
{
    use Queueable;

    protected $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Payment Confirmation')
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Your payment of GHS {$this->payment->amount} for booking #{$this->payment->booking_id} was successful.")
                    ->line('Thank you for booking with us!')
                    ->action('View Booking', url('/bookings/' . $this->payment->booking_id));
    }
}
