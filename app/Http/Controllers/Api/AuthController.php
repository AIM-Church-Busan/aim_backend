<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanningCenterUser;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // GET /api/auth/planning-center/redirect
    public function redirect()
    {
        return Socialite::driver('planning-center')
            ->stateless()
            ->redirect();
    }

    // GET /api/auth/planning-center/callback
    public function callback(Request $request)
    {
        $socialUser = Socialite::driver('planning-center')
            ->stateless()
            ->user();

        // planning_center_users 테이블 upsert
        $user = PlanningCenterUser::updateOrCreate(
            ['planning_center_id' => $socialUser->getId()],
            [
                'name'       => $socialUser->getName(),
                'email'      => $socialUser->getEmail(),
                'avatar_url' => $socialUser->getAvatar(),
            ]
        );

        Auth::guard('planning_center')->login($user);
        $request->session()->regenerate();

        return redirect(config('app.frontend_url'));
    }

    // POST /api/auth/logout
    public function logout(Request $request)
    {
        Auth::guard('planning_center')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        $user = Auth::guard('planning_center')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'avatar_url' => $user->avatar_url,
            'role'       => $user->role,
        ]);
    }
}
