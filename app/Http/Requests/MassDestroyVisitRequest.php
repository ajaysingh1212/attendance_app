<?php

namespace App\Http\Requests;

use App\Models\Visit;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyVisitRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('visit_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:visits,id',
        ];
    }
}
