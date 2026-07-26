<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Policy:
 * - Open to unauthenticated visitors (authorize() always returns true),
 *   same as contact_inquiries — no account required to subscribe.
 * - Minimal-collection principle: only email is collected, no name/phone/etc.
 */

class StoreSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email', 'max:255']];
    }
}
