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
    public function register(Event $event, int $planningCenterUserId): EventRegistration
    {
        // Check if already registered
        $existing = EventRegistration::where('event_id', $event->id)
            ->where('planning_center_user_id', $planningCenterUserId)
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

        return DB::transaction(function () use ($event, $planningCenterUserId, $existing) {
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
                'event_id'                => $event->id,
                'planning_center_user_id' => $planningCenterUserId,
                'status'                  => EventRegistration::STATUS_CONFIRMED,
            ]);
        });
    }

    /**
     * Cancel a user's registration for an event.
     * Increments remaining_spots back.
     */
    public function cancel(Event $event, int $planningCenterUserId): void
    {
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('planning_center_user_id', $planningCenterUserId)
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
    public function isRegistered(Event $event, int $planningCenterUserId): bool
    {
        return EventRegistration::where('event_id', $event->id)
            ->where('planning_center_user_id', $planningCenterUserId)
            ->where('status', '!=', EventRegistration::STATUS_CANCELLED)
            ->exists();
    }
}
