<?php

namespace App\Http\Requests;

use App\Models\Report;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateReportRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('performence_report_edit');
    }

    public function rules()
    {
        return [
            'date' => [
                'required',
               'date_format:Y-m',
            ],
            'sales' => [
                'required',
            ],
            'cost_of_sell' => [
                'string',
                'nullable',
            ],
            'metrial_cost' => [
                'string',
                'required',
            ],
            'salaries' => [
                'string',
                'required',
            ],
            'tour_travel' => [
                'string',
                'nullable',
            ],
            'other_cost' => [
                'string',
                'nullable',
            ],
            'unpaid_amount' => [
                'string',
                'nullable',
            ],
        ];
    }
}
