<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanningCenterUser;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

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

        // 기존 토큰 삭제 후 새 토큰 발급
        $user->tokens()->delete();
        $token = $user->createToken('planning-center-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'avatar_url' => $user->avatar_url,
                'role'       => $user->role,
            ],
        ]);
    }

    // POST /api/auth/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    // GET /api/auth/me
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'avatar_url' => $user->avatar_url,
            'role'       => $user->role,
        ]);
    }
}
