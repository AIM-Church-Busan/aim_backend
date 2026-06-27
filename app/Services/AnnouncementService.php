<?php

namespace App\Services;

use App\Models\Announcement;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class AnnouncementService
{
    /**
     * Get paginated list of published, active announcements.
     * Pinned announcements are always shown first.
     */
    public function getAnnouncements(?string $category = null, int $perPage = 12): LengthAwarePaginator
    {
        $query = Announcement::published()->active();

        if ($category) {
            $query->byCategory($category);
        }

        return $query
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * Find a single announcement by ID or fail with 404.
     */
    public function getAnnouncementById(int $id): Announcement
    {
        return Announcement::published()->active()->findOrFail($id);
    }

    /**
     * Create a new announcement.
     * Handles thumbnail file upload or external URL.
     */
    public function createAnnouncement(StoreAnnouncementRequest $request): Announcement
    {
        $data = $request->validated();
        $data = $this->handleThumbnail($data, $request);

        return Announcement::create($data);
    }

    /**
     * Update an existing announcement.
     * Replaces old thumbnail file if a new one is uploaded.
     */
    public function updateAnnouncement(Announcement $announcement, UpdateAnnouncementRequest $request): Announcement
    {
        $data = $request->validated();
        $data = $this->handleThumbnail($data, $request, $announcement);

        $announcement->update($data);

        return $announcement->fresh();
    }

    /**
     * Delete an announcement.
     */
    public function deleteAnnouncement(Announcement $announcement): void
    {
        if ($announcement->thumbnail_path) {
            Storage::disk('public')->delete($announcement->thumbnail_path);
        }

        $announcement->delete();
    }

    /**
     * Toggle pinned status of an announcement.
     */
    public function togglePin(Announcement $announcement): Announcement
    {
        $announcement->update(['is_pinned' => !$announcement->is_pinned]);

        return $announcement->fresh();
    }

    /**
     * Unpublish announcements whose due_date has passed.
     * Called by the Laravel scheduler daily.
     */
    public function expireAnnouncements(): int
    {
        return Announcement::published()
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
    private function handleThumbnail(array $data, $request, ?Announcement $announcement = null): array
    {
        if ($request->hasFile('thumbnail_path')) {
            if ($announcement?->thumbnail_path) {
                Storage::disk('public')->delete($announcement->thumbnail_path);
            }

            $data['thumbnail_path'] = $request->file('thumbnail_path')
                ->store('announcements/thumbnails', 'public');
            $data['thumbnail_url'] = null;

        } elseif (!empty($data['thumbnail_url'])) {
            if ($announcement?->thumbnail_path) {
                Storage::disk('public')->delete($announcement->thumbnail_path);
            }

            $data['thumbnail_path'] = null;
        }

        return $data;
    }
}
