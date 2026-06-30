<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-mail-preview', function () {
    return (new App\Mail\GenericMail(
        'John',
        'Thank you for reaching out to us.<br><br>We will get back to you shortly.',
        'Visit Our Website',
        'https://aimchurch.com'
    ))->render();
});

use App\Mail\NewInquiryNotification;

Route::get('/test-staff-mail-preview', function () {
    return (new NewInquiryNotification(
        'Jane Smith',
        'jane.smith@example.com',
        '010-1234-5678',
        'Hi, I would like to know more about your Sunday service times and whether you have a children\'s ministry program.',
        now()->format('F j, Y g:i A')
    ))->render();
});
