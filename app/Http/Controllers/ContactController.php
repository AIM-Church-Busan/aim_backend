<?php

namespace App\Http\Controllers;

use App\Mail\GenericMail;
use App\Mail\NewInquiryNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'phone'   => 'nullable|string|max:50',
            'message' => 'required|string',
        ]);

        // 1. DB 저장 (기존 로직)
        // Inquiry::create($validated);

        // 2. 문의자에게 감사 메일 발송
        Mail::to($validated['email'])->queue(new GenericMail(
            $validated['name'],
            'Thank you for reaching out to us.<br><br>We have received your message and our team will get back to you as soon as possible.',
            'Visit Our Website',
            'https://aimchurch.com'
        ));

        // 3. 교회 스태프에게 알림 메일 발송
        Mail::to(config('mail.church_contact_email'))->queue(new NewInquiryNotification(
            $validated['name'],
            $validated['email'],
            $validated['phone'] ?? null,
            $validated['message'],
            now()->format('F j, Y g:i A')
        ));

        return back()->with('success', 'Your message has been sent!');
    }
}
