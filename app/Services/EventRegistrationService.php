<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Database\QueryException;
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

            try {
                return EventRegistration::create([
                    'event_id'                => $event->id,
                    'planning_center_user_id' => $planningCenterUserId,
                    'status'                  => EventRegistration::STATUS_REGISTERED,
                ]);
            } catch (QueryException $e) {
                // Unique constraint violation (event_id, planning_center_user_id)
                // — happens if two requests race past the initial check above.
                if ($this->isUniqueViolation($e)) {
                    throw ValidationException::withMessages([
                        'event_id' => 'You have already registered for this event.',
                    ]);
                }

                throw $e;
            }
        });
    }

    public function cancel(Event $event, int $planningCenterUserId): void
    {
        DB::transaction(function () use ($event, $planningCenterUserId) {
            $registration = EventRegistration::where('event_id', $event->id)
                ->where('planning_center_user_id', $planningCenterUserId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($registration->isCancelled()) {
                throw ValidationException::withMessages([
                    'event_id' => 'This registration is already cancelled.',
                ]);
            }

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

    /**
     * Check if a QueryException is caused by a unique constraint violation,
     * across Postgres, MySQL, and SQLite.
     */
    private function isUniqueViolation(QueryException $e): bool
    {
        $code = $e->getCode();

        // Postgres: 23505, MySQL: 1062, SQLite: 19 (with "UNIQUE constraint failed" in message)
        return $code === '23505'
            || $code === '1062'
            || ($code === '19' && str_contains($e->getMessage(), 'UNIQUE constraint failed'));
    }
}
