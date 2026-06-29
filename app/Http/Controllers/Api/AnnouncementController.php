<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(
        private readonly AnnouncementService $announcementService,
    ) {}

    /**
     * GET /api/announcements
     * Return paginated list of published, active announcements.
     * Optionally filter by category via ?category=general|children|offering
     */
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $announcements = $this->announcementService->getAnnouncements($category);

        return response()->json($announcements);
    }

    /**
     * GET /api/announcements/{id}
     * Return a single announcement.
     */
    public function show(int $id): JsonResponse
    {
        $announcement = $this->announcementService->getAnnouncementById($id);

        return response()->json(['data' => $announcement]);
    }
}
