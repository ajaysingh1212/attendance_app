<?php

namespace App\Http\Requests;

use App\Models\LeaveRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('leave_request_create');
    }

    public function messages(): array
    {
        return ['date_from.after' => 'Aap current date ka leave apply nahi kar sakte. Please next day ya uske baad ki date select karein.'];
    }

    public function rules()
    {
        return [
            'title' => [
                'string',
                'nullable',
            ],
            'date_from' => ['required', 'date', 'after:today'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'leave_type_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
