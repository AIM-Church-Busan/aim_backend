<?php

namespace App\Http\Requests;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Required
            'title'            => ['sometimes', 'required', 'string', 'max:255'],
            'starts_at'        => ['sometimes', 'required', 'date'],

            // Optional — Date & Time
            'ends_at'          => ['nullable', 'date', 'after_or_equal:starts_at'],
            'start_time'       => ['nullable', 'date_format:H:i'],
            'end_time'         => ['nullable', 'date_format:H:i', 'after:start_time'],
            'due_date'         => ['nullable', 'date', 'after_or_equal:starts_at'],

            // Optional — Content
            'description'      => ['nullable', 'string'],
            'location'         => ['nullable', 'string', 'max:255'],
            'location_address' => ['nullable', 'string', 'max:255'],

            // Optional — Image (either file upload or URL, not both)
            'thumbnail_path'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'thumbnail_url'    => ['nullable', 'url', 'max:255'],

            // Category
            'category'         => ['sometimes', 'required', Rule::in(array_keys(Announcement::CATEGORIES))],

            // Publishing
            'is_pinned'        => ['boolean'],
            'is_published'     => ['boolean'],
            'published_at'     => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'          => 'Announcement title is required.',
            'starts_at.required'      => 'Announcement start date is required.',
            'ends_at.after_or_equal'  => 'End date must be on or after the start date.',
            'end_time.after'          => 'End time must be after start time.',
            'due_date.after_or_equal' => 'Due date must be on or after the start date.',
            'thumbnail_path.image'    => 'Thumbnail must be an image file.',
            'thumbnail_path.max'      => 'Thumbnail file size must not exceed 5MB.',
            'thumbnail_url.url'       => 'Thumbnail URL must be a valid URL.',
            'category.in'             => 'Category must be one of: General, Children, Offering.',
        ];
    }
}
