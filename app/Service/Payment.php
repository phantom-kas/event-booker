<?php

use App\Models\Booking;
use App\Models\Payment;

class PaymentService
{
    public function processPayment(Booking $booking)
    {
        // Simulate payment processing
        $success = rand(0, 1);
        
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_price,
            'status' => $success ? 'success' : 'failed'
        ]);

        return $payment;
    }
}

?>