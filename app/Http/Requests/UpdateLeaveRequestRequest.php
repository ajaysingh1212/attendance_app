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

    public function rules()
    {
        return [
            'title' => [
                'string',
                'nullable',
            ],
            'date_from' => [
                'string',
                'nullable',
            ],
            'date_to' => [
                'string',
                'nullable',
            ],
            'leave_type_id' => [
               'nullable',
                'integer',
                ],
        ];
    }
}
