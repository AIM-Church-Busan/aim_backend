<?php

namespace App\Services;

use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class EventService
{
    /**
     * Get paginated list of published, active, upcoming events.
     */
    public function getUpcomingEvents(int $perPage = 12): LengthAwarePaginator
    {
        return Event::published()
            ->active()
            ->upcoming()
            ->orderBy('starts_at')
            ->paginate($perPage);
    }

    /**
     * Find a single event by ID or fail with 404.
     */
    public function getEventById(int $id): Event
    {
        return Event::published()->active()->findOrFail($id);
    }

    /**
     * Create a new event.
     * Handles thumbnail file upload or external URL.
     */
    public function createEvent(StoreEventRequest $request): Event
    {
        $data = $request->validated();
        $data = $this->handleThumbnail($data, $request);
        $data['remaining_spots'] = $data['capacity'] ?? null;

        return Event::create($data);
    }

    /**
     * Update an existing event.
     * Replaces old thumbnail file if a new one is uploaded.
     */
    public function updateEvent(Event $event, UpdateEventRequest $request): Event
    {
        $data = $request->validated();
        $data = $this->handleThumbnail($data, $request, $event);

        // Sync remaining_spots if capacity changed
        if (isset($data['capacity']) && $data['capacity'] !== $event->capacity) {
            $registered = $event->registrations()->confirmed()->count();
            $data['remaining_spots'] = max(0, $data['capacity'] - $registered);
        }

        $event->update($data);

        return $event->fresh();
    }

    /**
     * Soft delete an event.
     */
    public function deleteEvent(Event $event): void
    {
        $event->delete();
    }

    /**
     * Unpublish events whose due_date has passed.
     * Called by the Laravel scheduler daily.
     */
    public function expireEvents(): int
    {
        return Event::published()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['is_published' => false]);
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Handle thumbnail file upload or URL.
     * If a file is uploaded, store it and clear thumbnail_url.
     * If a URL is provided, clear thumbnail_path.
     */
    private function handleThumbnail(array $data, $request, ?Event $event = null): array
    {
        if ($request->hasFile('thumbnail_path')) {
            // Delete old file if exists
            if ($event?->thumbnail_path) {
                Storage::disk('public')->delete($event->thumbnail_path);
            }

            $data['thumbnail_path'] = $request->file('thumbnail_path')
                ->store('events/thumbnails', 'public');
            $data['thumbnail_url'] = null;

        } elseif (!empty($data['thumbnail_url'])) {
            // Delete old file if switching to URL
            if ($event?->thumbnail_path) {
                Storage::disk('public')->delete($event->thumbnail_path);
            }

            $data['thumbnail_path'] = null;
        }

        return $data;
    }
}
