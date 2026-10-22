<?php

namespace App\Http\Requests;

use App\Models\LeaveRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateLeaveRequestRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('leave_request_edit');
    }

    public function messages(): array
    {
        return ['date_from.after' => 'Aap current date ka leave apply nahi kar sakte. Please next day ya uske baad ki date select karein.'];
    }

    public function rules()
    {
        $existing = $this->route('leaveRequest') ?? $this->route('leave_request');
        $unchangedDate = $existing instanceof LeaveRequest && $this->input('date_from') === $existing->date_from;
        return [
            'title' => [
                'string',
                'nullable',
            ],
            'date_from' => ['required', 'date', ($unchangedDate ? 'nullable' : 'after:today')],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'leave_type_id' => [
               'nullable',
                'integer',
                ],
        ];
    }
}
