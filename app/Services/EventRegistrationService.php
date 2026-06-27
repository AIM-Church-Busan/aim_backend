<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventRegistrationService
{
    /**
     * Register a user for an event.
     * Decrements remaining_spots within a transaction to prevent race conditions.
     */
    public function register(Event $event, int $userId): EventRegistration
    {
        // Check if already registered
        $existing = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing && !$existing->isCancelled()) {
            throw ValidationException::withMessages([
                'event_id' => 'You have already registered for this event.',
            ]);
        }

        // Check if event is full
        if ($event->is_full) {
            throw ValidationException::withMessages([
                'event_id' => 'This event is fully booked.',
            ]);
        }

        return DB::transaction(function () use ($event, $userId, $existing) {
            // Decrement remaining_spots if capacity is set
            if (!is_null($event->remaining_spots)) {
                $updated = Event::where('id', $event->id)
                    ->where('remaining_spots', '>', 0)
                    ->decrement('remaining_spots');

                if (!$updated) {
                    throw ValidationException::withMessages([
                        'event_id' => 'This event is fully booked.',
                    ]);
                }
            }

            // Re-register if previously cancelled
            if ($existing?->isCancelled()) {
                $existing->update(['status' => EventRegistration::STATUS_CONFIRMED]);
                return $existing->fresh();
            }

            return EventRegistration::create([
                'event_id' => $event->id,
                'user_id'  => $userId,
                'status'   => EventRegistration::STATUS_CONFIRMED,
            ]);
        });
    }

    /**
     * Cancel a user's registration for an event.
     * Increments remaining_spots back.
     */
    public function cancel(Event $event, int $userId): void
    {
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($registration->isCancelled()) {
            throw ValidationException::withMessages([
                'event_id' => 'This registration is already cancelled.',
            ]);
        }

        DB::transaction(function () use ($event, $registration) {
            $registration->update(['status' => EventRegistration::STATUS_CANCELLED]);

            // Restore remaining_spots if capacity is set
            if (!is_null($event->remaining_spots)) {
                $event->increment('remaining_spots');
            }
        });
    }

    /**
     * Check if a user is registered for an event.
     */
    public function isRegistered(Event $event, int $userId): bool
    {
        return EventRegistration::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->where('status', '!=', EventRegistration::STATUS_CANCELLED)
            ->exists();
    }
}
