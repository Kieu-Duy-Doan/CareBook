<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:appointment,result,system,reminder',
            'channels' => 'required|array',
            'channels.*' => 'in:in_web,email',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
        ];
    }
}
