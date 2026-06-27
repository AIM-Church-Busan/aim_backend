<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventLikeService;
use App\Services\EventRegistrationService;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService             $eventService,
        private readonly EventLikeService         $eventLikeService,
        private readonly EventRegistrationService $eventRegistrationService,
    ) {}

    /**
     * GET /api/events
     * Return paginated list of published, active, upcoming events.
     */
    public function index(): JsonResponse
    {
        $events = $this->eventService->getUpcomingEvents();

        return response()->json($events);
    }

    /**
     * GET /api/events/{id}
     * Return a single event with like/registration status for the authenticated user.
     */
    public function show(int $id): JsonResponse
    {
        $event = $this->eventService->getEventById($id);
        $userId = auth()->id();

        return response()->json([
            'data' => array_merge($event->toArray(), [
                'is_liked'      => $userId ? $this->eventLikeService->isLiked($event, $userId) : false,
                'like_count'    => $this->eventLikeService->getLikeCount($event),
                'is_registered' => $userId ? $this->eventRegistrationService->isRegistered($event, $userId) : false,
            ]),
        ]);
    }
}
