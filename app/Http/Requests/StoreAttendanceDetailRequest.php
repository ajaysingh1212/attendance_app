<?php

namespace App\Http\Requests;

use App\Models\AttendanceDetail;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreAttendanceDetailRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('attendance_detail_create');
    }

    public function rules()
    {
        return [
            'punch_in_time' => [
                'string',
                'nullable',
            ],
            'punch_in_latitude' => [
                'string',
                'nullable',
            ],
            'punch_in_longitude' => [
                'string',
                'nullable',
            ],
            'punch_in_location' => [
                'string',
                'nullable',
            ],
            'punch_out_time' => [
                'string',
                'nullable',
            ],
            'punch_out_latitude' => [
                'string',
                'nullable',
            ],
            'punch_out_longitude' => [
                'string',
                'nullable',
            ],
            'punch_out_location' => [
                'string',
                'nullable',
            ],
        ];
    }
}
