<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitApiRequest extends FormRequest
{
    public function authorize()
    {
        // API user submit ke liye always true
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'visited_counter_image' => 'nullable|file|image|max:2048',
            'visit_self_image' => 'nullable|file|image|max:2048',
            'visited_out_counter_image' => 'nullable|file|image|max:2048',
            'visited_out_self_image' => 'nullable|file|image|max:2048',
        ];
    }
}
