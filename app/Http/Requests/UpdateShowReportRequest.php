<?php

namespace App\Http\Requests;

use App\Models\ShowReport;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateShowReportRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('show_report_edit');
    }

    public function rules()
    {
        return [
            'select_employess_id' => [
                'required',
                'integer',
            ],
            'start_date' => [
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
                'nullable',
            ],
            'end_date' => [
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
                'nullable',
            ],
        ];
    }
}
