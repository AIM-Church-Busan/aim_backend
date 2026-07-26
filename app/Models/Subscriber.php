<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Policy
 *
 * - $fillable   : mass assignment(대량 할당) 허용 컬럼 화이트리스트.
 *                 email, token, is_confirmed, confirmed_at, unsubscribed_at 만
 *                 외부 입력으로 채울 수 있고 그 외 컬럼은 차단됨 (보안 목적).
 * - $casts      : DB 컬럼 값을 PHP 타입으로 자동 변환.
 *                 is_confirmed → boolean, confirmed_at / unsubscribed_at → Carbon 날짜 객체.
 * - booted()    : 모델 부팅 시 이벤트 리스너를 등록하는 훅.
 *   - creating  : 레코드가 INSERT 되기 직전에 실행됨.
 *                 token 값이 없을 때만(??=) Str::random(48)로 랜덤 토큰을 생성해 채움.
 *                 이 토큰은 로그인 없이 구독 확인 / 구독 취소 링크를 인증하는 용도로 쓰임.
 * - scopeConfirmed() : local scope. Subscriber::confirmed()로 체이닝 호출 가능.
 *                 is_confirmed = true 이면서 unsubscribed_at 이 null인
 *                 "확인 완료 & 구독 유지 중" 구독자만 조회하는 재사용 필터.
 */

class Subscriber extends Model
{
    protected $fillable = [
        'email', 'token', 'is_confirmed', 'confirmed_at', 'unsubscribed_at'
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Subscriber $subscriber) {
            $subscriber->token ??= Str::random(48);
        });
    }

    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true)->whereNull('unsubscribed_at');
    }
}
