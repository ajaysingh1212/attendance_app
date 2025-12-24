<?php

namespace App\Http\Requests;

use App\Models\AppUpdate;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyAppUpdateRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('app_update_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:app_updates,id',
        ];
    }
}
