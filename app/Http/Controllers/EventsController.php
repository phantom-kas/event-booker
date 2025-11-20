<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Traits\PaginationTrait;
use App\Traits\ValidatesRequestsJson;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    //


    use PaginationTrait, ValidatesRequestsJson;
    public function index(Request $request)
    {
        $events = Event::query()
            ->with('organizer:id,name')
            ->withCount('tickets as total_tickets')
            ->addSelect([
                'tickets_available' => \App\Models\Ticket::selectRaw('SUM(quantity)')
                    ->whereColumn('tickets.event_id', 'events.id')
            ])

            ->search($request->get('search'))
            ->filterByLocation($request->get('location'))
            ->filterByDate(
                $request->get('date_from'),
                $request->get('date_to')
            )
            ->when($request->boolean('upcoming'), fn($q) => $q->upcoming())

            ->latest('date')
            ->paginate($request->get('per_page', 10));

        return $this->paginatedResponse($events);
    }



    public function show($id)
    {
        // Load event + organizer + tickets
        $event = Event::with([
            'organizer:id,name,email',
            'tickets:id,event_id,type,price,quantity'
        ])->find($id);

        // Handle not found
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    public function store(Request $request)
    {


        // Validate input

        $validated = $this->validateJson($request, [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'location'    => 'required|string|max:255',
            'start_date'  => 'required|date|after:now',
            'end_date'    => 'required|date|after:start_date',
            'capacity'    => 'required|integer|min:1',
            'price'       => 'required|numeric|min:0',
        ]);

        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }

        // Create event
        $event = Event::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'location'    => $validated['location'],
            'start_date'  => $validated['start_date'],
            'end_date'    => $validated['end_date'],
            'capacity'    => $validated['capacity'],
            'price'       => $validated['price'],
            'date' => now(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully.',
            'data'    => $event
        ], 201);
    }









    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.'
            ], 404);
        }

        if ($event->created_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => "You are not allowed to update this event since you didn't create it."
            ], 403);
        }

        // Validation

        $validated = $this->validateJson($request, [
            'title'       => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'date'        => 'sometimes|required|date',
            'location'    => 'sometimes|required|string',
            'start_date'   => 'sometimes|required|date',
            'end_date'     => 'sometimes|required|date|after_or_equal:start_date',
        ]);
        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }

        // Update only provided fields
        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully.',
            'event'   => $event,
        ]);
    }




    public function destroy(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'message' => 'Event not found'
            ], 404);
        }

        if ($event->created_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => "You are not allowed to Delete this event since you didn't create it."
            ], 403);
        }


        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ], 200);
    }
}
