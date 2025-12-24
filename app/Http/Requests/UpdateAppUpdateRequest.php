<?php

namespace App\Http\Requests;

use App\Models\AppUpdate;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateAppUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('app_update_edit');
    }

    public function rules()
    {
        return [
            'version' => [
                'string',
                'required',
            ],
            'heading' => [
                'string',
                'required',
            ],
            'content' => [
                'required',
            ],
            'app' => [
                'required',
            ],
        ];
    }
}
