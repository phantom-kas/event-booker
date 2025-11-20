<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\BookingConfirmed;
use App\Traits\PaginationTrait;
use App\Traits\ValidatesRequestsJson;
use Illuminate\Http\Request;


class BookingsController extends Controller
{
    use PaginationTrait, ValidatesRequestsJson;

    public function index(Request $request)
    {
        $user = $request->user(); // or auth()->user()

        $bookings = Booking::with([
            'ticket:id,type,price,event_id',
            'ticket.event:id,title,date,location'
        ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return $this->paginatedResponse($bookings);
    }
    public function book(Request $request, $id)
    {
        // Find the ticket
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ], 404);
        }

        // Validate the quantity requested
        $validated = $this->validateJson($request, [
            'quantity' => 'required|integer|min:1|max:' . $ticket->quantity
        ]);

        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }
        // Optional: Prevent overbooking
        $existingBookings = $ticket->bookings()->sum('quantity');
        if ($existingBookings + $validated['quantity'] > $ticket->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough tickets available.'
            ], 400);
        }

        // Create booking
        $booking = Booking::create([
            'user_id'   => $request->user()->id, // or auth()->id()
            'ticket_id' => $ticket->id,
            'quantity'  => $validated['quantity'],
            'status'    => 'pending' // default status
        ]);
        $user = User::find($request->user()->id);
        $user->notify((new BookingConfirmed($booking))->delay(now()->addSeconds(5)));

        return response()->json([
            'success' => true,
            'booking' => $booking
        ], 201);
    }













    public function cancel(Request $request, $id)
    {
        $user = $request->user();

        // Only customers can cancel their bookings


        // Find booking and ensure it belongs to the authenticated user
        $booking = Booking::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }


        if ($booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already cancelled.'
            ], 400);
        }

        // Update status to cancelled
        $booking->status = 'cancelled';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
            'booking' => $booking
        ]);
    }
}
