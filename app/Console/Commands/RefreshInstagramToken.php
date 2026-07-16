<?php

namespace App\Console\Commands;

use App\Models\InstagramToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshInstagramToken extends Command
{
    protected $signature = 'instagram:refresh-token';
    protected $description = '만료 10일 이내로 남은 Instagram long-lived token을 자동 갱신';

    public function handle(): int
    {
        $tokens = InstagramToken::where('expires_at', '<=', now()->addDays(10))->get();

        if ($tokens->isEmpty()) {
            $this->info('갱신이 필요한 토큰이 없습니다.');
            return self::SUCCESS;
        }

        $hasFailure = false;

        foreach ($tokens as $token) {
            $response = Http::get('https://graph.instagram.com/refresh_access_token', [
                'grant_type'   => 'ig_refresh_token',
                'access_token' => $token->access_token,
            ]);

            if (!$response->successful()) {
                $hasFailure = true;
                $this->error("토큰 갱신 실패 (instagram_user_id: {$token->instagram_user_id}): {$response->body()}");
                Log::error('Instagram 토큰 갱신 실패', [
                    'instagram_user_id' => $token->instagram_user_id,
                    'body' => $response->body(),
                ]);
                continue;
            }

            $data = $response->json();

            $token->update([
                'access_token' => $data['access_token'],
                'expires_at'   => now()->addSeconds($data['expires_in']),
            ]);

            $this->info("토큰 갱신 완료 (instagram_user_id: {$token->instagram_user_id}), 새 만료일: {$token->expires_at}");
            Log::info('Instagram 토큰 갱신 완료', [
                'instagram_user_id' => $token->instagram_user_id,
                'new_expires_at' => $token->expires_at,
            ]);
        }

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }
}
