<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SermonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SermonController extends Controller
{
    public function __construct(
        private readonly SermonService $sermonService,
    ) {}

    /** GET /api/sermons?page=1&title=... */
    public function index(Request $request): JsonResponse
    {
        $page  = (int) $request->query('page', 1);
        $title = $request->query('title');

        return response()->json($this->sermonService->getSermons($page, $title));
    }

    /** GET /api/sermons/live */
    public function live(): JsonResponse
    {
        return response()->json(['data' => $this->sermonService->getLiveSermon()]);
    }

    /** GET /api/sermons/upcoming */
    public function upcoming(): JsonResponse
    {
        return response()->json(['data' => $this->sermonService->getUpcomingSermon()]);
    }

    /** GET /api/sermons/{id} (YouTube video ID) */
    public function show(string $id): JsonResponse
    {
        return response()->json(['data' => $this->sermonService->getSermonById($id)]);
    }
}
