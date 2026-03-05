<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    // Could extract this logic into a ReservationService for cleaner separation.
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {
            $reservation = DB::transaction(function () use ($validated) {
                // Lock the event row to prevent race conditions under concurrent requests
                $event = Event::where('id', $validated['event_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // I usually avoid premature optimisation as I said but at scale 
                // I could use a `reservations_count` column on events (O(1) vs O(n) count).
                // then: if ($event->reservations_count >= $event->capacity) return null;
                // followed by $event->increment('reservations_count') after create.
                $currentCount = Reservation::where('event_id', $event->id)->count();

                // Caching this count isn't ideal here, cache can desync with lockForUpdate.
                if ($currentCount >= $event->capacity) {
                    return null;
                }

                // for very large datasets: partition the reservations table.
                return Reservation::create($validated);
            });

            if (! $reservation) {
                return response()->json(['message' => 'Event has reached capacity.'], 409);
            }

            return response()->json([
                'message' => 'Reservation created.',
                'reservation' => $reservation,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create reservation.'], 500);
        }
    }
}
