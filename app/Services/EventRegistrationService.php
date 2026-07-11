<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventRegistrationService
{
    public function register(Event $event, int $planningCenterUserId): EventRegistration
    {
        $existing = EventRegistration::where('event_id', $event->id)
            ->where('planning_center_user_id', $planningCenterUserId)
            ->first();

        if ($existing && !$existing->isCancelled()) {
            throw ValidationException::withMessages([
                'event_id' => 'You have already registered for this event.',
            ]);
        }

        if ($event->is_full) {
            throw ValidationException::withMessages([
                'event_id' => 'This event is fully booked.',
            ]);
        }

        return DB::transaction(function () use ($event, $planningCenterUserId, $existing) {
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

            if ($existing?->isCancelled()) {
                $existing->update(['status' => EventRegistration::STATUS_REGISTERED]);
                return $existing->fresh();
            }

            return EventRegistration::create([
                'event_id'                => $event->id,
                'planning_center_user_id' => $planningCenterUserId,
                'status'                  => EventRegistration::STATUS_REGISTERED,
            ]);
        });
    }

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

            if (!is_null($event->remaining_spots)) {
                $event->increment('remaining_spots');
            }
        });
    }

    public function isRegistered(Event $event, int $planningCenterUserId): bool
    {
        return EventRegistration::where('event_id', $event->id)
            ->where('planning_center_user_id', $planningCenterUserId)
            ->where('status', '!=', EventRegistration::STATUS_CANCELLED)
            ->exists();
    }
}
