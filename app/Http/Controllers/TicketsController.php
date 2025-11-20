<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Traits\PaginationTrait;
use App\Traits\ValidatesRequestsJson;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    //

    use PaginationTrait, ValidatesRequestsJson;

    public function store(Request $request, $event_id)
    {
        $event = Event::find($event_id);

        if (!$event) {
            return response()->json([
                'message' => 'Event not found.'
            ], 404);
        }

        // Ensure organizer owns the event
        if ($event->created_by !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden. You can only add tickets to your own events.'
            ], 403);
        }

        // Validate input

        $validated = $this->validateJson($request, [
            'type'     => 'required|string',
            'price'    => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }
        $ticket = Ticket::create([
            'event_id' => $event->id,
            'type'     => $validated['type'],
            'price'    => $validated['price'],
            'quantity' => $validated['quantity'],
        ]);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'ticket'  => $ticket,
        ], 201);
    }









    public function update(Request $request, $id)
    {
        // Find the ticket
        $ticket = Ticket::findOrFail($id);

        // Check if the authenticated user owns the event
        if ($ticket->event->created_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this ticket.'
            ], 403);
        }

        // Validate input
        $validated = $this->validateJson($request, [
            'type'     => 'sometimes|string',
            'price'    => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
        ]);
        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }

        // Update ticket
        $ticket->update($validated);

        return response()->json([
            'success' => true,
            'ticket'  => $ticket
        ]);
    }





     public function destroy(Request $request, $id)
    {
        // Find the ticket
        $ticket = Ticket::find($id);
        if(!$ticket){
             return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }
        // Check if the authenticated user owns the event
        if ($ticket->event->created_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this ticket.'
            ], 403);
        }

        // Delete the ticket
        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket deleted successfully.'
        ]);
    }
}
