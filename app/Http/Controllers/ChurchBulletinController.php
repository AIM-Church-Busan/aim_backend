<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChurchBulletin;

/**
 * Policy:
 * - Public, read-only endpoints (no auth) — mirrors the Announcements/Events
 *   public listing pattern.
 * - Responses expose pdf_url (accessor on the model), never pdf_path, so the
 *   storage disk can change (public -> R2) without breaking API consumers.
 */
class ChurchBulletinController extends Controller
{
    public function index()
    {
        return ChurchBulletin::query()
            ->orderByDesc('created_at')
            ->get();
    }

    public function show(ChurchBulletin $bulletin)
    {
        return $bulletin;
    }
}
