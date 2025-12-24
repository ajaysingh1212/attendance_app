<?php

namespace App\Http\Requests;

use App\Models\ShowReport;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyShowReportRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('show_report_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:show_reports,id',
        ];
    }
}
