<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpecialtyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('specialties')->ignore($id)],
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên chuyên khoa.',
            'name.unique' => 'Tên chuyên khoa đã tồn tại.',
            'display_order.min' => 'Thứ tự không hợp lệ.',
        ];
    }
}
