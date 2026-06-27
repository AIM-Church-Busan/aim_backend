<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRegistrationRequest;
use App\Models\Event;
use App\Models\PlanningCenterUser;
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
        $planningCenterUser = PlanningCenterUser::where('planning_center_id', auth()->user()->planning_center_id)->firstOrFail();

        $registration = $this->eventRegistrationService->register($event, $planningCenterUser->id);

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
        $planningCenterUser = PlanningCenterUser::where('planning_center_id', auth()->user()->planning_center_id)->firstOrFail();

        $this->eventRegistrationService->cancel($event, $planningCenterUser->id);

        return response()->json([
            'message' => 'Registration successfully cancelled.',
        ]);
    }
}
