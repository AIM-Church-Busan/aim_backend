<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('youtube:subscribe')->days([0])->at('00:00'); // 매주 일요일 자정 — 5일 만료보다 여유 있게 갱신
