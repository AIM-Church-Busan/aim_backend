<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRegistrationRequest;
use App\Models\Event;
use App\Services\EventRegistrationService;
use Illuminate\Http\JsonResponse;

class EventRegistrationController extends Controller
{
    public function __construct(
        private readonly EventRegistrationService $eventRegistrationService,
    ) {}

    /**
     * POST /api/events/{event}/register
     * Register the authenticated user for an event.
     */
    public function store(StoreEventRegistrationRequest $request, Event $event): JsonResponse
    {
        $registration = $this->eventRegistrationService->register($event, auth()->id());

        return response()->json([
            'message'      => 'Successfully registered for the event.',
            'registration' => $registration,
        ], 201);
    }

    /**
     * DELETE /api/events/{event}/register
     * Cancel the authenticated user's registration for an event.
     */
    public function cancel(Event $event): JsonResponse
    {
        $this->eventRegistrationService->cancel($event, auth()->id());

        return response()->json([
            'message' => 'Registration successfully cancelled.',
        ]);
    }
}
