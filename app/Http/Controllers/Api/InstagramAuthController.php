<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstagramToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramAuthController extends Controller
{
    // GET /api/instagram/auth/redirect
    public function redirect()
    {
        $query = http_build_query([
            'client_id'     => config('services.instagram.client_id'),
            'redirect_uri'  => config('services.instagram.redirect'),
            'response_type' => 'code',
            'scope'         => 'instagram_business_basic',
        ]);

        return redirect("https://www.instagram.com/oauth/authorize?{$query}");
    }

    // GET /api/instagram/auth/callback
    public function callback(Request $request)
    {
        $code = $request->query('code');

        if (!$code) {
            Log::warning('Instagram OAuth callback: code 없음', ['query' => $request->query()]);
            return response()->json(['error' => 'Missing authorization code'], 400);
        }

        // 1. code → short-lived access token 교환
        $shortLivedResponse = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
            'client_id'     => config('services.instagram.client_id'),
            'client_secret' => config('services.instagram.client_secret'),
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => config('services.instagram.redirect'),
            'code'          => $code,
        ]);

        if (!$shortLivedResponse->successful()) {
            Log::error('Instagram short-lived token 교환 실패', ['body' => $shortLivedResponse->body()]);
            return response()->json(['error' => 'Token exchange failed'], 500);
        }

        $shortLivedData = $shortLivedResponse->json();
        $shortLivedToken = $shortLivedData['access_token'];
        $instagramUserId = $shortLivedData['user_id'];

        // 2. short-lived → long-lived token 교환 (60일 유효)
        $longLivedResponse = Http::get('https://graph.instagram.com/access_token', [
            'grant_type'    => 'ig_exchange_token',
            'client_secret' => config('services.instagram.client_secret'),
            'access_token'  => $shortLivedToken,
        ]);

        if (!$longLivedResponse->successful()) {
            Log::error('Instagram long-lived token 교환 실패', ['body' => $longLivedResponse->body()]);
            return response()->json(['error' => 'Long-lived token exchange failed'], 500);
        }

        $longLivedData = $longLivedResponse->json();

        // 3. DB 저장 (upsert)
        InstagramToken::updateOrCreate(
            ['instagram_user_id' => $instagramUserId],
            [
                'access_token' => $longLivedData['access_token'],
                'expires_at'   => now()->addSeconds($longLivedData['expires_in']),
            ]
        );

        Log::info('Instagram 토큰 발급 및 저장 완료', ['instagram_user_id' => $instagramUserId]);

        return response()->json(['message' => 'Instagram account connected successfully.']);
    }
}
