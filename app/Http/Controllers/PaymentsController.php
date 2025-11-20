<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\PaymentSuccess;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    //

    public function pay(Request $request, $id)
    {
        $user = $request->user();



        // Find booking and ensure it belongs to the authenticated user
        $booking = Booking::with('ticket')->where('id', $id)->where('user_id', $user->id)->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }

        // Check if booking is already paid
        if ($booking->payment && $booking->payment->status === 'success') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already paid.'
            ], 400);
        }
        if ($booking->status == 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is cancelled.'
            ], 400);
        }

        $mockSuccess = true;
        $status = $mockSuccess ? 'success' : 'failed';


        $payment = $booking->payment()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'amount' => $booking->ticket->price * $booking->quantity,
                'status' => $status
            ]
        );

        // Update booking status if payment succeeded
        if ($status === 'success') {
            $booking->status = 'confirmed';
            // $user->notify(new PaymentSuccessful($payment));
            $user->notify((new PaymentSuccess($payment))->delay(now()->addSeconds(5)));

            $booking->save();
        }



        return response()->json([
            'success' => $mockSuccess,
            'message' => $mockSuccess ? 'Payment successful' : 'Payment failed',
            'payment' => $payment,
            'booking' => $booking
        ]);
    }









    public function show($id, Request $request)
    {
        $user = $request->user();

        $payment = Payment::with('booking.ticket.event')->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.'
            ], 404);
        }

        if ($payment->booking->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this payment.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'payment' => $payment
        ]);
    }
}
