<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InstagramFeedService;
use Illuminate\Http\JsonResponse;

class InstagramController extends Controller
{
    public function __construct(
        private readonly InstagramFeedService $instagramFeedService,
    ) {}

    // GET /api/instagram/feed
    public function feed(): JsonResponse
    {
        return response()->json([
            'data' => $this->instagramFeedService->getFeed(),
        ]);
    }
}
